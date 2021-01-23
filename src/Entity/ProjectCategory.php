<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectCategoryRepository")
 * @ORM\HasLifecycleCallbacks
 * @Vich\Uploadable()
 */
class ProjectCategory
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30, unique=true)
     * @Assert\Length(max="30", maxMessage="Longueur max 30 caractères pour l'URL")
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(max="255", maxMessage="Longueur max 255 caractères pour le nom")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(max="255", maxMessage="Longueur max 255 caractères pour la description")
     */
    private $description;

    /**
     * @ORM\Column(type="boolean")
     */
    private $visible;

    /**
     * @ORM\Column(type="integer")
     */
    private $listOrder = 100;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="category_logo", fileNameProperty="logoFileName")
     * @Assert\Image(mimeTypesMessage="Le format du logo est invalide")
     * @Serializer\Exclude
     */
    private $logoFile;

    /**
     * var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $logoFileName;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Project", mappedBy="category")
     * @Serializer\Exclude()
     */
    private $projects;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * @return File|null
     */
    public function getLogoFile(): ?File {
        return $this->logoFile;
    }

    /**
     * @param File|null $logoFile
     * @return ProjectCategory
     */
    public function setLogoFile(?File $logoFile): ProjectCategory {
        $this->logoFile = $logoFile;
        return $this;
    }

    public function getLogoFileName() {
        return $this->logoFileName;
    }

    public function setLogoFileName($logoFileName): ProjectCategory {
        $this->logoFileName = $logoFileName;
        return $this;
    }

    /**
     * @return int
     */
    public function getListOrder(): int
    {
        return $this->listOrder;
    }

    /**
     * @param int $listOrder
     * @return ProjectCategory
     */
    public function setListOrder(int $listOrder): ProjectCategory
    {
        $this->listOrder = $listOrder;
        return $this;
    }

    /**
     * @return Collection|Project[]
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
            $project->setCategory($this);
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->contains($project)) {
            $this->projects->removeElement($project);
            // set the owning side to null (unless already changed)
            if ($project->getCategory() === $this) {
                $project->setCategory(null);
            }
        }

        return $this;
    }
}
