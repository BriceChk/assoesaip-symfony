<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
     * @Groups({"basic"})
     */
    private $id;

    /**
     * @Assert\NotBlank(
     *     message="Le nom du projet ne peut pas être vide"
     * )
     * @Assert\Length(
     *     max = 50,
     *     maxMessage = "Le nom du projet ne doit pas dépasser {{ limit }} caractères"
     * )
     * @ORM\Column(type="string", length=50)
     * @Groups({"basic"})
     */
    private $name;

    /**
     * @Assert\NotBlank(
     *     message="L'URL ne peut pas être vide"
     * )
     * @Assert\Length(
     *     max = 30,
     *     maxMessage = "L'URL ne doit pas dépasser {{ limit }} caractères"
     * )
     * @ORM\Column(type="string", length=30, unique=true)
     * @Groups({"basic"})
     */
    private $url;

    /**
     * @Assert\Choice(
     *     choices = {"Association", "Club", "Projet", "Liste BDE"},
     *     message = "Le type de projet est invalide"
     * )
     * @ORM\Column(type="string", length=15)
     * @Groups({"basic"})
     */
    private $type;

    /**
     * @Assert\NotNull
     * @ORM\ManyToOne(targetEntity="App\Entity\ProjectCategory", inversedBy="projects")
     */
    private $category;

    /**
     * @Assert\Choice(
     *     choices = {"Angers", "Aix", "Reims"},
     *     message = "Le campus est invalide"
     * )
     * @ORM\Column(type="string", length=6)
     * @Groups({"basic"})
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
     * @Assert\Length(
     *     max = 255,
     *     maxMessage = "La totalité des mots clés ne peut pas dépasser 255 caractères"
     * )
     * @ORM\Column(type="string", length=255)
     */
    private $keywords;

    /**
     * @Assert\Email(
     *     message = "Cette adresse mail n'est pas valide"
     * )
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $social = [];

    /**
     * @Assert\Length(
     *     max = 180,
     *     maxMessage="La description ne doit pas dépasser 180 caractères"
     * )
     * @ORM\Column(type="string", length=180)
     */
    private $description;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="project_logo", fileNameProperty="logoFileName")
     * @Assert\Image(mimeTypesMessage="Le format du logo est invalide")
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

    /**
     * @ORM\OneToMany(targetEntity=UploadedImage::class, mappedBy="project", cascade={"remove"})
     */
    private $uploadedImages;

    public function __construct()
    {
        $this->childrenProjects = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->roomBooks = new ArrayCollection();
        $this->pages = new ArrayCollection();
        $this->news = new ArrayCollection();
        $this->uploadedImages = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist()
     */
    public function onPrePersist() {
        $this->dateAdded = $this->dateModified = new DateTime();
    }

    /**
     * @ORM\PreUpdate()
     */
    public function onPreUpdate() {
        $this->dateModified = new DateTime();
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
        if ($logoFile instanceof UploadedFile) {
            $this->dateModified = new DateTime('now');
        }
        return $this;
    }

    public function getLogoFileName() {
        return $this->logoFileName;
    }

    public function setLogoFileName($logoFileName) {
        $this->logoFileName = $logoFileName;
        return $this;
    }

    public function getDateAdded(): ?DateTimeInterface
    {
        return $this->dateAdded;
    }

    public function setDateAdded(DateTimeInterface $dateAdded): self
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    public function getDateModified(): ?DateTimeInterface
    {
        return $this->dateModified;
    }

    public function setDateModified(DateTimeInterface $dateModified): self
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

    /**
     * @return Collection|UploadedImage[]
     */
    public function getUploadedImages(): Collection
    {
        return $this->uploadedImages;
    }

    public function addUploadedImage(UploadedImage $uploadedImage): self
    {
        if (!$this->uploadedImages->contains($uploadedImage)) {
            $this->uploadedImages[] = $uploadedImage;
            $uploadedImage->setProject($this);
        }

        return $this;
    }

    public function removeUploadedImage(UploadedImage $uploadedImage): self
    {
        if ($this->uploadedImages->removeElement($uploadedImage)) {
            // set the owning side to null (unless already changed)
            if ($uploadedImage->getProject() === $this) {
                $uploadedImage->setProject(null);
            }
        }

        return $this;
    }
}
