<?php

namespace App\Entity;

use App\Repository\ContratFichierRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContratFichierRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ContratFichier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'fichiers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Contrat $contrat = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom du fichier est obligatoire')]
    private ?string $nomOriginal = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le chemin du fichier est obligatoire')]
    private ?string $chemin = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $typeMime = null;

    #[ORM\Column(nullable: true)]
    private ?int $taille = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContrat(): ?Contrat
    {
        return $this->contrat;
    }

    public function setContrat(?Contrat $contrat): static
    {
        $this->contrat = $contrat;
        return $this;
    }

    public function getNomOriginal(): ?string
    {
        return $this->nomOriginal;
    }

    public function setNomOriginal(string $nomOriginal): static
    {
        $this->nomOriginal = $nomOriginal;
        return $this;
    }

    public function getChemin(): ?string
    {
        return $this->chemin;
    }

    public function setChemin(string $chemin): static
    {
        $this->chemin = $chemin;
        return $this;
    }

    public function getTypeMime(): ?string
    {
        return $this->typeMime;
    }

    public function setTypeMime(?string $typeMime): static
    {
        $this->typeMime = $typeMime;
        return $this;
    }

    public function getTaille(): ?int
    {
        return $this->taille;
    }

    public function setTaille(?int $taille): static
    {
        $this->taille = $taille;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
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

    /**
     * Retourne la taille formatee (Ko, Mo)
     */
    public function getTailleFormatee(): string
    {
        if ($this->taille === null) {
            return '-';
        }

        if ($this->taille < 1024) {
            return $this->taille . ' o';
        }

        if ($this->taille < 1024 * 1024) {
            return round($this->taille / 1024, 1) . ' Ko';
        }

        return round($this->taille / (1024 * 1024), 1) . ' Mo';
    }

    /**
     * Retourne l'extension du fichier
     */
    public function getExtension(): string
    {
        return pathinfo($this->nomOriginal, PATHINFO_EXTENSION);
    }

    /**
     * Verifie si c'est un PDF
     */
    public function isPdf(): bool
    {
        return strtolower($this->getExtension()) === 'pdf' ||
            $this->typeMime === 'application/pdf';
    }
}
