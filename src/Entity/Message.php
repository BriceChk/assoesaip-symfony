<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MessageRepository::class)
 */
class Message
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="text", length=65535)
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $messageDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPrevention;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getMessageDate(): ?\DateTimeInterface
    {
        return $this->messageDate;
    }

    public function setMessageDate(\DateTimeInterface $messageDate): self
    {
        $this->messageDate = $messageDate;

        return $this;
    }

    public function getIsPrevention(): ?bool
    {
        return $this->isPrevention;
    }

    public function setIsPrevention(bool $isPrevention): self
    {
        $this->isPrevention = $isPrevention;

        return $this;
    }
}
