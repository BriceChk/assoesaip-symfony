<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectPageRepository")
 */
class ProjectPage
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"list", "page-full"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     * @Assert\Length(max="30", maxMessage="Le nom de la page ne doit pas dépasser 30 caractères")
     * @Groups({"list"})
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="pages")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"page-full"})
     */
    private $project;

    /**
     * @ORM\Column(type="text")
     * @Groups({"list", "page-full"})
     */
    private $html;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"list", "page-full"})
     */
    private $orderPosition;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"list", "page-full"})
     */
    private $published;

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

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

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

    public function getOrderPosition(): ?int
    {
        return $this->orderPosition;
    }

    public function setOrderPosition(int $orderPosition): self
    {
        $this->orderPosition = $orderPosition;

        return $this;
    }

    public function getPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;

        return $this;
    }
}
