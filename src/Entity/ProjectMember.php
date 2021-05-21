<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectMemberRepository")
 */
class ProjectMember
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="projects")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(message="User invalide")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="members")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(message="Projet invalide")
     * @Serializer\Exclude()
     */
    private $project;

    /**
     * @ORM\Column(type="string", length=40)
     * @Assert\Length(max=40, maxMessage="Le rôle ne doit pas dépasser 40 caractères")
     */
    private $role = 'Membre';

    /**
     * @ORM\Column(type="string", length=500)
     * @Assert\Length(max=500, maxMessage="L'introduction ne doit pas faire plus de 500 caractères")
     */
    private $introduction = '';

    /**
     * @ORM\Column(type="boolean")
     */
    private $admin;

    /**
     * @ORM\Column(type="integer")
     */
    private $orderPosition = 1;

    public function getId(): ?int {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getIntroduction(): ?string
    {
        return $this->introduction;
    }

    public function setIntroduction(string $introduction): self
    {
        $this->introduction = $introduction;

        return $this;
    }

    public function isAdmin(): ?bool
    {
        return $this->admin;
    }

    public function setAdmin(bool $admin): self
    {
        $this->admin = $admin;

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
}
