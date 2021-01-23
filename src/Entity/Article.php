<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArticleRepository")
 */
class Article
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"news", "article"})
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Groups({"news", "article"})
     */
    private $url = "";

    /**
     * @ORM\Column(type="string", length=60)
     * @Assert\Length(max="60", maxMessage="Le titre ne doit pas dépasser 60 caractères")
     * @Groups({"news", "article"})
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="articles")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"article"})
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="articles")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"article"})
     */
    private $project;

    /**
     * @ORM\Column(type="string", length=180)
     * @Assert\Length(max="180", maxMessage="Le résumé ne doit pas dépasser 180 caractères")
     * @Groups({"news", "article"})
     */
    private $abstract;

    /**
     * @ORM\Column(type="text")
     * @Groups({"article"})
     */
    private $html;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"article"})
     */
    private $dateCreated;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"article"})
     */
    private $dateEdited;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"article"})
     */
    private $datePublished;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"article"})
     */
    private $published;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"article"})
     */
    private $private;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ArticleCategory", inversedBy="articles")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"article"})
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity=UploadedImage::class, mappedBy="article", cascade={"remove"} )
     * @Serializer\Exclude
     */
    private $uploadedImages;

    /**
     * @ORM\OneToOne(targetEntity=News::class, mappedBy="article", cascade={"remove"})
     * @Serializer\Exclude
     */
    private $news;

    public function __construct()
    {
        $this->uploadedImages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl() {
        return $this->url;
    }

    public function setUrl($url) {
        $this->url = $url;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;
        return $this;
    }

    public function getAbstract(): ?string
    {
        return $this->abstract;
    }

    public function setAbstract(string $abstract): self
    {
        $this->abstract = $abstract;
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

    public function getDatePublished(): ?\DateTimeInterface
    {
        return $this->datePublished;
    }

    public function setDatePublished(\DateTimeInterface $datePublished): self
    {
        $this->datePublished = $datePublished;
        return $this;
    }

    public function getDateEdited() {
        return $this->dateEdited;
    }

    public function setDateEdited($dateEdited) {
        $this->dateEdited = $dateEdited;
        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;
        return $this;
    }

    public function isPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;
        return $this;
    }

    public function isPrivate() {
        return $this->private;
    }

    public function setPrivate($private) {
        $this->private = $private;
        return $this;
    }

    public function getCategory(): ?ArticleCategory
    {
        return $this->category;
    }

    public function setCategory(?ArticleCategory $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getCampus() {
        return $this->project->getCampus();
    }

    public function getNews() {
        return $this->news;
    }

    /**
     * @param News $news
     * @return Article
     */
    public function setNews(News $news): Article {
        $this->news = $news;
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
            $uploadedImage->setArticle($this);
        }

        return $this;
    }

    public function removeUploadedImage(UploadedImage $uploadedImage): self
    {
        if ($this->uploadedImages->removeElement($uploadedImage)) {
            // set the owning side to null (unless already changed)
            if ($uploadedImage->getArticle() === $this) {
                $uploadedImage->setArticle(null);
            }
        }

        return $this;
    }
}
