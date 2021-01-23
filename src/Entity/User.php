<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks
 * @Vich\Uploadable()
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"article", "event", "profile"})
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Groups({"article", "event", "profile"})
     */
    private $firstName;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Groups({"article", "event", "profile"})
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", unique=true)
     * @Assert\Email()
     * @Groups({"article", "event", "profile"})
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $msId;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\Column(type="string", length=15)
     * @Groups({"article", "event", "profile", "profileEdit"})
     * @Assert\Choice({"ING1", "ING2", "IR3", "IR4", "IR5", "IRA3", "IRA4", "IRA5", "SEP3", "SEP4", "SEP5", "SEPA3", "SEPA4", "SEPA5", "CPI", "BACH1", "BACH2", "BACH3", "Personnel esaip"})
     */
    private $promo = '';

    /**
     * @ORM\Column(type="string", length=15)
     * @Groups({"article", "event", "profile", "profileEdit"})
     * @Assert\Choice({"Angers", "Aix"})
     */
    private $campus = '';

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="profile_pic", fileNameProperty="avatarFileName")
     * @Assert\Image()
     * @Serializer\Exclude()
     */
    private $avatarFile;

    /**
     * var string
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"article", "event", "profile"})
     */
    private $avatarFileName;

    /**
     * @ORM\Column(type="date")
     * @Serializer\Exclude()
     */
    private $firstLogin;

    /**
     * @param mixed $firstLogin
     * @return User
     */
    public function setFirstLogin($firstLogin) {
        $this->firstLogin = $firstLogin;
        return $this;
    }

    /**
     * @param mixed $lastLogin
     * @return User
     */
    public function setLastLogin($lastLogin) {
        $this->lastLogin = $lastLogin;
        return $this;
    }

    /**
     * @ORM\Column(type="date")
     * @Serializer\Exclude()
     */
    private $lastLogin;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProjectMember", mappedBy="user", orphanRemoval=true)
     * @Serializer\Exclude()
     */
    private $projects;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Article", mappedBy="author")
     * @Serializer\Exclude()
     */
    private $articles;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Event", mappedBy="author")
     * @Serializer\Exclude()
     */
    private $events;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RoomBook", mappedBy="user")
     * @Serializer\Exclude()
     */
    private $roomBooks;

    /**
     * @ORM\OneToMany(targetEntity=FcmToken::class, mappedBy="user", orphanRemoval=true)
     * @Serializer\Exclude()
     */
    private $fcmTokens;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->roomBooks = new ArrayCollection();
        $this->fcmTokens = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist() {
        $this->firstLogin = $this->lastLogin = new DateTime('NOW');
    }

    public function getId() {
        return $this->id;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function setFirstName($firstName): void {
        $this->firstName = $firstName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function setLastName($lastName): void {
        $this->lastName = $lastName;
    }

    public function getFullName() {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @Serializer\VirtualProperty()
     * @return string
     */
    public function getFullNameAndEmail() {
        return $this->getFullName() . ' (' . $this->username . ')';
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username): void {
        $this->username = $username;
    }

    public function getEmail() {
        return $this->username;
    }

    /**
     * @return File|null
     */
    public function getAvatarFile(): ?File {
        return $this->avatarFile;
    }

    /**
     * @param File|null $avatarFile
     * @return User
     */
    public function setAvatarFile(?File $avatarFile): User {
        $this->avatarFile = $avatarFile;

        if ($this->avatarFile instanceof UploadedFile) {
            $this->lastLogin = new DateTime('NOW');
        }

        return $this;
    }

    public function getAvatarFileName() {
        return $this->avatarFileName;
    }

    public function setAvatarFileName($avatarFileName) {
        $this->avatarFileName = $avatarFileName;
        return $this;
    }

    public function getPromo() {
        return $this->promo;
    }

    public function setPromo($promo): void {
        $this->promo = $promo;
    }

    public function getCampus() {
        return $this->campus;
    }

    public function setCampus($campus): void {
        $this->campus = $campus;
    }

    public function getFirstLogin() {
        return $this->firstLogin;
    }

    public function getLastLogin() {
        return $this->lastLogin;
    }

    public function updateLastLogin(): void {
        $this->lastLogin = new DateTime('now');
    }

    public function getRoles(): array {
        $roles = $this->roles;

        // Identified user must have at least one role, default is ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): void {
        $this->roles = $roles;
    }

    public function getSalt(): ?string {
        return null;
    }

    public function eraseCredentials(): void {
    }

    public function getPassword() {
    }

    /**
     * @return Collection|ProjectMember[]
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    /**
     * @return Project[]|Collection
     */
    public function getProjectsIfAdmin()
    {
        return $this->getProjects()->filter(function (ProjectMember $project) {
            return $project->isAdmin();
        })->map(function (ProjectMember $project) {
            return $project->getProject();
        });
    }

    public function addProject(ProjectMember $member): self
    {
        if (!$this->projects->contains($member)) {
            $this->projects[] = $member;
            $member->setUser($this);
        }

        return $this;
    }

    public function removeProject(ProjectMember $member): self
    {
        if ($this->projects->contains($member)) {
            $this->projects->removeElement($member);
            // set the owning side to null (unless already changed)
            if ($member->getUser() === $this) {
                $member->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Article[]
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles[] = $article;
            $article->setAuthor($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->contains($article)) {
            $this->articles->removeElement($article);
            // set the owning side to null (unless already changed)
            if ($article->getAuthor() === $this) {
                $article->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setAuthor($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            // set the owning side to null (unless already changed)
            if ($event->getAuthor() === $this) {
                $event->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|RoomBook[]
     */
    public function getRoomBooks(): Collection
    {
        return $this->roomBooks;
    }

    public function addRoomBook(RoomBook $roomBook): self
    {
        if (!$this->roomBooks->contains($roomBook)) {
            $this->roomBooks[] = $roomBook;
            $roomBook->setUser($this);
        }

        return $this;
    }

    public function removeRoomBook(RoomBook $roomBook): self
    {
        if ($this->roomBooks->contains($roomBook)) {
            $this->roomBooks->removeElement($roomBook);
            // set the owning side to null (unless already changed)
            if ($roomBook->getUser() === $this) {
                $roomBook->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|FcmToken[]
     */
    public function getFcmTokens(): Collection
    {
        return $this->fcmTokens;
    }

    public function addFcmToken(FcmToken $fcmToken): self
    {
        if (!$this->fcmTokens->contains($fcmToken)) {
            $this->fcmTokens[] = $fcmToken;
            $fcmToken->setUser($this);
        }

        return $this;
    }

    public function removeFcmToken(FcmToken $fcmToken): self
    {
        if ($this->fcmTokens->removeElement($fcmToken)) {
            // set the owning side to null (unless already changed)
            if ($fcmToken->getUser() === $this) {
                $fcmToken->setUser(null);
            }
        }

        return $this;
    }

    public function getMsId(): ?string
    {
        return $this->msId;
    }

    public function setMsId(string $msId): self
    {
        $this->msId = $msId;

        return $this;
    }
}
