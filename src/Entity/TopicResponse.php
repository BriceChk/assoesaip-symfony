<?php

namespace App\Entity;

use App\Repository\TopicResponseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TopicResponseRepository::class)
 */
class TopicResponse
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $responseDate;

    /**
     * @ORM\Column(type="text", length=65535)
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\Column(type="text", length=65535, nullable=true)
     */
    private $rejectionMessage;

    /**
     * @ORM\ManyToOne(targetEntity=Topic::class, inversedBy="topicReponses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $topic;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="topicReponses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAnonymous;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isSeen;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResponseDate(): ?\DateTimeInterface
    {
        return $this->responseDate;
    }

    public function setResponseDate(\DateTimeInterface $responseDate): self
    {
        $this->responseDate = $responseDate;

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

    public function getTopic(): ?Topic
    {
        return $this->topic;
    }

    public function setTopic(?Topic $topic): self
    {
        $this->topic = $topic;

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

    public function getIsAnonymous(): ?bool
    {
        return $this->isAnonymous;
    }

    public function setIsAnonymous(bool $isAnonymous): self
    {
        $this->isAnonymous = $isAnonymous;

        return $this;
    }

    public function getIsSeen(): ?bool
    {
        return $this->isSeen;
    }

    public function setIsSeen(bool $isSeen): self
    {
        $this->isSeen = $isSeen;

        return $this;
    }
}
