<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NewsRepository")
 */
class News
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"news"})
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(max="240", maxMessage="Le message ne doit pas dépasser 240 caractères")
     * @Groups({"news"})
     */
    private $content = "";

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max="255", maxMessage="Le lien ne doit pas dépasser 255 caractères")
     * @Assert\Url(message="L'url est invalide")
     * @Groups({"news"})
     */
    private $link;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Article", inversedBy="news")
     * @Groups({"news"})
     */
    private $article;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Event", inversedBy="news")
     * @Groups({"news"})
     */
    private $event;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"news"})
     */
    private $datePublished;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="news")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"news"})
     */
    private $project;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"news"})
     */
    private $starred = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link == "" ? null : $link;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getDatePublished(): ?\DateTimeInterface
    {
        return $this->datePublished;
    }

    public function setDatePublished($datePublished): self
    {
        $this->datePublished = $datePublished;

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

    public function getCampus() {
        return $this->project->getCampus();
    }

    public function getStarred(): ?bool
    {
        return $this->starred;
    }

    public function setStarred(bool $starred): self
    {
        $this->starred = $starred;

        return $this;
    }
}
