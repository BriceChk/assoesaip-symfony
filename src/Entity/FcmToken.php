<?php

namespace App\Entity;

use App\Repository\FcmTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=FcmTokenRepository::class)
 */
class FcmToken
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="fcmTokens")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=22)
     * @Assert\Length(exactMessage="L'ID d'instance doit faire 22 caractères.", max="22", min="22")
     * @Groups({"fcmtoken"})
     */
    private $instanceId;

    /**
     * @ORM\Column(type="string", length=163)
     * @Assert\Length(exactMessage="Le token (instanceId:token) doit faire 163 caractères.", max="163", min="163")
     * @Groups({"fcmtoken"})
     */
    private $token;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"fcmtoken"})
     */
    private $notificationsEnabled = false;

    public function getId(): ?int
    {
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

    public function getInstanceId(): ?string
    {
        return $this->instanceId;
    }

    public function setInstanceId(string $instanceId): self
    {
        $this->instanceId = $instanceId;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function isNotificationsEnabled(): ?bool
    {
        return $this->notificationsEnabled;
    }

    public function setNotificationsEnabled(bool $notificationsEnabled): self
    {
        $this->notificationsEnabled = $notificationsEnabled;

        return $this;
    }
}
