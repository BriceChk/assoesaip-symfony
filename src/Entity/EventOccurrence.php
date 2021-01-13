<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventOccurrenceRepository")
 */
class EventOccurrence
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"event", "eventOccList"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="occurrences")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"eventOccList"})
     */
    private $event;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"event", "eventOccList"})
     */
    private $date;

    /**
     * EventOccurrence constructor.
     * @param $date
     */
    public function __construct($date) {
        $this->date = $date;
    }


    public function getId(): ?int
    {
        return $this->id;
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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }
}
