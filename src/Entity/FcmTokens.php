<?php

namespace App\Entity;

use App\Repository\FcmTokensRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FcmTokensRepository::class)
 */
class FcmTokens
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="fcmTokens")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $instanceId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $token;

    public function __construct()
    {
        $this->user = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|User[]
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(User $user): self
    {
        if (!$this->user->contains($user)) {
            $this->user[] = $user;
            $user->setFcmTokens($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->user->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getFcmTokens() === $this) {
                $user->setFcmTokens(null);
            }
        }

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
}
