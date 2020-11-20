<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoomBookRepository")
 */
class RoomBook
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="roomBooks")
     * @ORM\JoinColumn(nullable=false)
     * @MaxDepth(1)
     * @Assert\NotNull(message="Le projet associé n'est pas valide")
     */
    private $project;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="roomBooks")
     * @ORM\JoinColumn(nullable=false)
     * @MaxDepth(1)
     * @Assert\NotNull(message="L'utilisateur associé n'est pas valide")
     */
    private $user;

    /**
     * @ORM\Column(type="date")
     * @Assert\GreaterThan(value="+ 4 days", message="La date doit être éloignée d'au moins 5 jours")
     */
    private $date;

    /**
     * @ORM\Column(type="time")
     */
    private $startTime;

    /**
     * @ORM\Column(type="time")
     */
    private $endTime;

    /**
     * @ORM\Column(type="integer")
     * @Assert\GreaterThanOrEqual(value="1", message="Le nombre de participants doit être supérieur ou égal à 1")
     */
    private $nbParticipants;

    /**
     * @ORM\Column(type="string", length=1000)
     * @Assert\Length(max="1000", maxMessage="L'objet ne doit pas dépaser 1000 caractères")
     * @Assert\NotBlank(message="L'objet ne peut pas être vide")
     */
    private $object;

    /**
     * @ORM\Column(type="string", length=1000)
     * @Assert\Length(max="1000", maxMessage="La section demandes ne doit pas dépaser 1000 caractères")
     */
    private $needs;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\Choice(
     *     choices = {"En attente", "Acceptée", "Refusée"},
     *     message = "Le statut doit être un des suivants : {{ choices }}"
     * )
     */
    private $status = 'En attente';

    /**
     * @ORM\Column(type="string", length=10)
     * @Assert\Length(max=10, maxMessage="La salle ne doit pas dépasser 10 caractères")
     */
    private $room = '';

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getNbParticipants(): ?int
    {
        return $this->nbParticipants;
    }

    public function setNbParticipants(int $nbParticipants): self
    {
        $this->nbParticipants = $nbParticipants;

        return $this;
    }

    public function getObject(): ?string
    {
        return $this->object;
    }

    public function setObject(string $object): self
    {
        $this->object = $object;

        return $this;
    }

    public function getNeeds(): ?string
    {
        return $this->needs;
    }

    public function setNeeds(string $needs): self
    {
        $this->needs = $needs;

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

    public function getRoom(): ?string
    {
        return $this->room;
    }

    public function setRoom(string $room): self
    {
        $this->room = $room;

        return $this;
    }
}
