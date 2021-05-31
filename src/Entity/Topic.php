<?php

namespace App\Entity;

use Webmozart\Assert\Assert;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TopicRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\Count;

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
     * @ORM\Column(type="text", length=65535)
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
     * @ORM\ManyToMany(targetEntity=Tag::class, inversedBy="topics")
     * @Assert\Count(min="1", max="1")
     */
    private $tags;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\Column(type="text", length=65535, nullable=true)
     */
    private $rejectionMessage;

    /**
     * @ORM\OneToMany(targetEntity=TopicResponse::class, mappedBy="topic")
     */
    private $topicResponses;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAnonymous;

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

    public function getLastUpdate(): ?\DateTimeInterface
    {
        $lastUpdate = $this->getCreationDate();
        foreach ($this->topicResponses as $topicResponse) {
            if ($lastUpdate == $this->getCreationDate()) {
                $lastUpdate = $topicResponse->getResponseDate();
            } else if ($lastUpdate < $topicResponse->getResponseDate()) {
                $lastUpdate = $topicResponse->getResponseDate();
            }
        }
        return $lastUpdate;
    }

    public function getIsAnonymous(): ?bool
    {
        return $this->isAnonymous;
    }

    public function setIsAnonymous(bool $isAnonymous): self
    {
        $this->isAnonymous = $isAnonymous;

        return $this;
    }
}
