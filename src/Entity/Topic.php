<?php

namespace App\Entity;

use App\Repository\TopicRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TopicRepository::class)
 */
class Topic
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $creationDate;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="topics")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\ManyToMany(targetEntity=tag::class, inversedBy="topics")
     */
    private $tags;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $rejectionMessage;

    /**
     * @ORM\OneToMany(targetEntity=TopicResponse::class, mappedBy="topic")
     */
    private $topicResponses;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->topicResponses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;

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

    /**
     * @return Collection|tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getRejectionMessage(): ?string
    {
        return $this->rejectionMessage;
    }

    public function setRejectionMessage(string $rejectionMessage): self
    {
        $this->rejectionMessage = $rejectionMessage;

        return $this;
    }

    /**
     * @return Collection|TopicResponse[]
     */
    public function getTopicResponses(): Collection
    {
        return $this->topicResponses;
    }

    public function addTopicResponse(TopicResponse $topicResponse): self
    {
        if (!$this->topicResponses->contains($topicResponse)) {
            $this->topicResponses[] = $topicResponse;
            $topicResponse->setTopic($this);
        }

        return $this;
    }

    public function removeTopicResponse(TopicResponse $topicResponse): self
    {
        if ($this->topicResponses->removeElement($topicResponse)) {
            // set the owning side to null (unless already changed)
            if ($topicResponse->getTopic() === $this) {
                $topicResponse->setTopic(null);
            }
        }

        return $this;
    }
}