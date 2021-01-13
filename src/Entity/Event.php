<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 */
class Event
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"news", "event", "eventOccList"})
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Groups({"news", "event"})
     */
    private $url = "";

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\Length(max="50", maxMessage="Le titre ne doit pas dépasser 50 caractères")
     * @Groups({"news", "event", "eventOccList"})
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"event"})
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"event", "eventOccList"})
     */
    private $project;

    /**
     * @ORM\Column(type="string", length=180)
     * @Assert\Length(max="180", maxMessage="Le résumé ne doit pas dépasser 180 caractères")
     * @Groups({"news", "event", "eventOccList"})
     */
    private $abstract;

    /**
     * @ORM\Column(type="text")
     * @Groups({"event"})
     */
    private $html;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"event"})
     */
    private $dateCreated;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"event"})
     */
    private $dateEdited;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"event"})
     */
    private $datePublished;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"event"})
     */
    private $published;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"event"})
     */
    private $private;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EventCategory", inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"event", "eventOccList"})
     */
    private $category;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"event", "eventOccList"})
     */
    private $dateStart;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"event", "eventOccList"})
     */
    private $dateEnd;

    /**
     * @ORM\Column(type="integer")
     * Duration in minutes
     * @Groups({"event", "eventOccList"})
     */
    private $duration;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"event", "eventOccList"})
     */
    private $allDay;

    /**
     * @ORM\Column(type="json")
     * @Groups({"event", "eventOccList"})
     */
    private $daysOfWeek = [];

    /**
     * @ORM\Column(type="integer")
     * @Groups({"event", "eventOccList"})
     */
    private $intervalCount;

    /**
     * @ORM\Column(type="string", length=8)
     * @Groups({"event", "eventOccList"})
     */
    private $intervalType;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"event", "eventOccList"})
     */
    private $occurrencesCount;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EventOccurrence", mappedBy="event", orphanRemoval=true, cascade={"persist"})
     * @Groups({"event"})
     */
    private $occurrences;

    /**
     * @ORM\OneToMany(targetEntity=UploadedImage::class, mappedBy="event", cascade={"remove"})
     * @Serializer\Exclude
     */
    private $uploadedImages;

    /**
     * @ORM\OneToOne(targetEntity=News::class, mappedBy="event", cascade={"remove"})
     * @Serializer\Exclude
     */
    private $news;

    public function __construct()
    {
        $this->occurrences = new ArrayCollection();
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

    public function getCategory(): ?EventCategory
    {
        return $this->category;
    }

    public function setCategory(?EventCategory $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setDateStart(\DateTimeInterface $dateStart): self
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(?\DateTimeInterface $dateEnd): self
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function isAllDay(): ?bool
    {
        return $this->allDay;
    }

    public function setAllDay(bool $allDay): self
    {
        $this->allDay = $allDay;

        return $this;
    }

    public function getDaysOfWeek(): ?array
    {
        return $this->daysOfWeek;
    }

    public function setDaysOfWeek(array $daysOfWeek): self
    {
        $this->daysOfWeek = $daysOfWeek;

        return $this;
    }

    public function getIntervalCount(): ?int
    {
        return $this->intervalCount;
    }

    public function setIntervalCount(int $intervalCount): self
    {
        $this->intervalCount = $intervalCount;

        return $this;
    }

    public function getIntervalType(): ?string
    {
        return $this->intervalType;
    }

    public function setIntervalType(string $intervalType): self
    {
        $this->intervalType = $intervalType;

        return $this;
    }

    public function getOccurrencesCount(): ?int
    {
        return $this->occurrencesCount;
    }

    public function setOccurrencesCount(int $occurrencesCount): self
    {
        $this->occurrencesCount = $occurrencesCount;

        return $this;
    }

    /**
     * @return Collection|EventOccurrence[]
     */
    public function getOccurrences(): Collection
    {
        return $this->occurrences;
    }

    public function removePastOccurrences(): self
    {
        $this->occurrences = $this->occurrences->filter(function (EventOccurrence $occ) {
            if ($this->allDay) {
                return $occ->getDate() >= (new DateTime('now'))->setTime(0, 0, 0);
            }
            return $occ->getDate() >= new DateTime('now');
        });
        return $this;
    }

    public function addOccurrence(EventOccurrence $occurrence): self
    {
        if (!$this->occurrences->contains($occurrence)) {
            $this->occurrences[] = $occurrence;
            $occurrence->setEvent($this);
        }

        return $this;
    }

    public function removeOccurrence(EventOccurrence $occurrence): self
    {
        if ($this->occurrences->contains($occurrence)) {
            $this->occurrences->removeElement($occurrence);
            // set the owning side to null (unless already changed)
            if ($occurrence->getEvent() === $this) {
                $occurrence->setEvent(null);
            }
        }

        return $this;
    }

    public function getCampus() {
        return $this->project->getCampus();
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
            $uploadedImage->setEvent($this);
        }

        return $this;
    }

    public function removeUploadedImage(UploadedImage $uploadedImage): self
    {
        if ($this->uploadedImages->removeElement($uploadedImage)) {
            // set the owning side to null (unless already changed)
            if ($uploadedImage->getEvent() === $this) {
                $uploadedImage->setEvent(null);
            }
        }

        return $this;
    }
}
