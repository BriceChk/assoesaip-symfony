<?php

namespace App\Entity;

use App\Repository\UploadedImageRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=UploadedImageRepository::class)
 * @ORM\HasLifecycleCallbacks
 * @Vich\Uploadable()
 */
class UploadedImage
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="uploaded_image", fileNameProperty="fileName")
     * @Assert\Image(mimeTypesMessage="Le format de l'image est invalide")
     */
    private $file;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $fileName;

    /**
     * @ORM\ManyToOne(targetEntity=Project::class, inversedBy="uploadedImages")
     */
    private $project;

    /**
     * @ORM\ManyToOne(targetEntity=ProjectPage::class, inversedBy="uploadedImages")
     */
    private $projectPage;

    /**
     * @ORM\ManyToOne(targetEntity=Article::class, inversedBy="uploadedImages")
     */
    private $article;

    /**
     * @ORM\ManyToOne(targetEntity=Event::class, inversedBy="uploadedImages")
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity=RessourcePage::class, inversedBy="uploadedImages")
     */
    private $ressourcePage;

    /**
     * @ORM\Column(type="date")
     */
    private $dateModified;

    /**
     * @ORM\PrePersist()
     */
    public function onPrePersist() {
        $this->dateModified = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return File|null
     */
    public function getFile(): ?File {
        return $this->file;
    }

    /**
     * @param File|null $file
     * @return UploadedImage
     */
    public function setFile(?File $file): UploadedImage {
        $this->file = $file;
        if ($file instanceof UploadedFile) {
            $this->dateModified = new DateTime('now');
        }
        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;

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

    public function getProjectPage(): ?ProjectPage
    {
        return $this->projectPage;
    }

    public function setProjectPage(?ProjectPage $projectPage): self
    {
        $this->projectPage = $projectPage;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
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

    public function getRessourcePage(): ?RessourcePage
    {
        return $this->ressourcePage;
    }

    public function setRessourcePage(?RessourcePage $ressourcePage): self
    {
        $this->ressourcePage = $ressourcePage;

        return $this;
    }

    public function getDateModified(): ?\DateTimeInterface
    {
        return $this->dateModified;
    }

    public function setDateModified(\DateTimeInterface $dateModified): self
    {
        $this->dateModified = $dateModified;

        return $this;
    }
}
