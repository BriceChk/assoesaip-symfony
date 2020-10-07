<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRepository")
 * @ORM\HasLifecycleCallbacks
 * @Vich\Uploadable()
 */
class Project
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=30, unique=true)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProjectCategory", inversedBy="projects")
     */
    private $category;

    /**
     * @ORM\Column(type="string", length=35)
     */
    private $campus;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="childrenProjects")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $parentProject;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Project", mappedBy="parentProject")
     * @Serializer\Exclude()
     */
    private $childrenProjects;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $keywords;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $social = [];

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $description;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="project_logo", fileNameProperty="logoFileName")
     * @Assert\Image()
     */
    private $logoFile;

    /**
    * var string
    * @ORM\Column(type="string", nullable=true)
    */
    private $logoFileName;

    /**
     * @ORM\Column(type="text")
     */
    private $html;

    /**
     * @ORM\Column(type="date")
     */
    private $dateAdded;

    /**
     * @ORM\Column(type="date")
     */
    private $dateModified;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProjectMember", mappedBy="project", orphanRemoval=true)
     * @Serializer\Exclude()
     */
    private $members;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Article", mappedBy="project", orphanRemoval=true)
     * @Serializer\Exclude()
     */
    private $articles;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Event", mappedBy="project", orphanRemoval=true)
     * @Serializer\Exclude()
     */
    private $events;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RoomBook", mappedBy="project", orphanRemoval=true)
     * @Serializer\Exclude()
     */
    private $roomBooks;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProjectPage", mappedBy="project", orphanRemoval=true)
     * @Serializer\Exclude()
     */
    private $pages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\News", mappedBy="project", orphanRemoval=true)
     * @Serializer\Exclude()
     */
    private $news;

    public function __construct()
    {
        $this->childrenProjects = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->roomBooks = new ArrayCollection();
        $this->pages = new ArrayCollection();
        $this->news = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist()
     */
    public function onPrePersist() {
        $this->dateAdded = $this->dateModified = date('Y-m-d');
    }

    /**
     * @ORM\PreUpdate()
     */
    public function onPreUpdate() {
        $this->dateModified = date('Y-m-d');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory(?ProjectCategory $category): void
    {
        $this->category = $category;
    }

    public function getCampus(): ?string
    {
        return $this->campus;
    }

    public function setCampus(string $campus): self
    {
        $this->campus = $campus;

        return $this;
    }

    public function getParentProject(): ?self
    {
        return $this->parentProject;
    }

    public function setParentProject(?self $parentProject): self
    {
        $this->parentProject = $parentProject;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getChildrenProjects(): Collection
    {
        return $this->childrenProjects;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function setKeywords(string $keywords): self
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getSocial(): array
    {
        return $this->social;
    }

    public function setSocial(array $social): self
    {
        $this->social = $social;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getHtml(): ?string
    {
        return $this->html;
    }

    public function setHtml(string $html): self
    {
        $this->html = $html;

        return $this;
    }

    /**
     * @return File|null
     */
    public function getLogoFile(): ?File {
        return $this->logoFile;
    }

    /**
     * @param File|null $logoFile
     * @return Project
     */
    public function setLogoFile(?File $logoFile): Project {
        $this->logoFile = $logoFile;
        return $this;
    }

    public function getLogoFileName() {
        return $this->logoFileName;
    }

    public function setLogoFileName($logoFileName) {
        $this->logoFileName = $logoFileName;
        return $this;
    }

    public function getDateAdded(): ?\DateTimeInterface
    {
        return $this->dateAdded;
    }

    public function setDateAdded(\DateTimeInterface $dateAdded): self
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    public function getDateModified(): ?\DateTimeInterface
    {
        return $this->dateModified;
    }

    public function setDateModified(\DateTimeInterface $dateModified): self
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    /**
     * @return Collection|ProjectMember[]
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(ProjectMember $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members[] = $member;
            $member->setProject($this);
        }

        return $this;
    }

    public function removeMember(ProjectMember $member): self
    {
        if ($this->members->contains($member)) {
            $this->members->removeElement($member);
            // set the owning side to null (unless already changed)
            if ($member->getProject() === $this) {
                $member->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Article[]
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles[] = $article;
            $article->setProject($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->contains($article)) {
            $this->articles->removeElement($article);
            // set the owning side to null (unless already changed)
            if ($article->getProject() === $this) {
                $article->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setProject($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            // set the owning side to null (unless already changed)
            if ($event->getProject() === $this) {
                $event->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|RoomBook[]
     */
    public function getRoomBooks(): Collection
    {
        return $this->roomBooks;
    }

    public function addRoomBook(RoomBook $roomBook): self
    {
        if (!$this->roomBooks->contains($roomBook)) {
            $this->roomBooks[] = $roomBook;
            $roomBook->setProject($this);
        }

        return $this;
    }

    public function removeRoomBook(RoomBook $roomBook): self
    {
        if ($this->roomBooks->contains($roomBook)) {
            $this->roomBooks->removeElement($roomBook);
            // set the owning side to null (unless already changed)
            if ($roomBook->getProject() === $roomBook) {
                $roomBook->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProjectPage[]
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage(ProjectPage $projectPage): self
    {
        if (!$this->pages->contains($projectPage)) {
            $this->pages[] = $projectPage;
            $projectPage->setProject($this);
        }

        return $this;
    }

    public function removePage(ProjectPage $projectPage): self
    {
        if ($this->pages->contains($projectPage)) {
            $this->pages->removeElement($projectPage);
            // set the owning side to null (unless already changed)
            if ($projectPage->getProject() === $this) {
                $projectPage->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|News[]
     */
    public function getNews(): Collection
    {
        return $this->news;
    }

    public function addNews(News $news): self
    {
        if (!$this->news->contains($news)) {
            $this->news[] = $news;
            $news->setProject($this);
        }

        return $this;
    }

    public function removeNews(News $news): self
    {
        if ($this->news->contains($news)) {
            $this->news->removeElement($news);
            // set the owning side to null (unless already changed)
            if ($news->getProject() === $this) {
                $news->setProject(null);
            }
        }

        return $this;
    }
}
