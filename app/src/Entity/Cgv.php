<?php

namespace App\Entity;

use App\Repository\CgvRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CgvRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Cgv
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom de la version CGV est obligatoire')]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $fichierChemin = null;

    #[ORM\Column(length: 255)]
    private ?string $fichierOriginal = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date de debut est obligatoire')]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getFichierChemin(): ?string
    {
        return $this->fichierChemin;
    }

    public function setFichierChemin(string $fichierChemin): static
    {
        $this->fichierChemin = $fichierChemin;
        return $this;
    }

    public function getFichierOriginal(): ?string
    {
        return $this->fichierOriginal;
    }

    public function setFichierOriginal(string $fichierOriginal): static
    {
        $this->fichierOriginal = $fichierOriginal;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function isActif(): bool
    {
        $now = new \DateTime();

        if ($this->dateDebut > $now) {
            return false;
        }

        if ($this->dateFin && $this->dateFin < $now) {
            return false;
        }

        return true;
    }

    public function getStatut(): string
    {
        $now = new \DateTime();

        if ($this->dateDebut > $now) {
            return 'A venir';
        }

        if ($this->dateFin && $this->dateFin < $now) {
            return 'Expire';
        }

        return 'Actif';
    }

    public function getPeriode(): string
    {
        $debut = $this->dateDebut?->format('d/m/Y') ?? '-';
        $fin = $this->dateFin?->format('d/m/Y') ?? '...';
        return $debut . ' - ' . $fin;
    }
}
