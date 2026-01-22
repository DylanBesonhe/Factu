<?php

namespace App\Entity;

use App\Repository\EmetteurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmetteurRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Emetteur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    #[Assert\NotBlank(message: 'Le code est obligatoire')]
    #[Assert\Length(max: 20, maxMessage: 'Le code ne peut pas depasser {{ limit }} caracteres')]
    private ?string $code = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    private ?string $nom = null;

    #[ORM\Column]
    private bool $actif = true;

    #[ORM\Column]
    private bool $parDefaut = false;

    #[ORM\OneToMany(mappedBy: 'emetteur', targetEntity: EmetteurVersion::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['dateEffet' => 'DESC'])]
    private Collection $versions;

    #[ORM\OneToOne(mappedBy: 'emetteur', targetEntity: ParametreFacturation::class, cascade: ['persist', 'remove'])]
    private ?ParametreFacturation $parametreFacturation = null;

    #[ORM\OneToMany(mappedBy: 'emetteur', targetEntity: EmetteurCgv::class, cascade: ['persist', 'remove'])]
    private Collection $cgvAssociations;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->versions = new ArrayCollection();
        $this->cgvAssociations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = strtoupper($code);
        return $this;
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

    public function isActif(): bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;
        return $this;
    }

    public function isParDefaut(): bool
    {
        return $this->parDefaut;
    }

    public function setParDefaut(bool $parDefaut): static
    {
        $this->parDefaut = $parDefaut;
        return $this;
    }

    /**
     * @return Collection<int, EmetteurVersion>
     */
    public function getVersions(): Collection
    {
        return $this->versions;
    }

    public function addVersion(EmetteurVersion $version): static
    {
        if (!$this->versions->contains($version)) {
            $this->versions->add($version);
            $version->setEmetteur($this);
        }
        return $this;
    }

    public function removeVersion(EmetteurVersion $version): static
    {
        if ($this->versions->removeElement($version)) {
            if ($version->getEmetteur() === $this) {
                $version->setEmetteur(null);
            }
        }
        return $this;
    }

    public function getParametreFacturation(): ?ParametreFacturation
    {
        return $this->parametreFacturation;
    }

    public function setParametreFacturation(?ParametreFacturation $parametreFacturation): static
    {
        if ($parametreFacturation === null && $this->parametreFacturation !== null) {
            $this->parametreFacturation->setEmetteur(null);
        }

        if ($parametreFacturation !== null && $parametreFacturation->getEmetteur() !== $this) {
            $parametreFacturation->setEmetteur($this);
        }

        $this->parametreFacturation = $parametreFacturation;
        return $this;
    }

    /**
     * @return Collection<int, EmetteurCgv>
     */
    public function getCgvAssociations(): Collection
    {
        return $this->cgvAssociations;
    }

    public function addCgvAssociation(EmetteurCgv $cgvAssociation): static
    {
        if (!$this->cgvAssociations->contains($cgvAssociation)) {
            $this->cgvAssociations->add($cgvAssociation);
            $cgvAssociation->setEmetteur($this);
        }
        return $this;
    }

    public function removeCgvAssociation(EmetteurCgv $cgvAssociation): static
    {
        if ($this->cgvAssociations->removeElement($cgvAssociation)) {
            if ($cgvAssociation->getEmetteur() === $this) {
                $cgvAssociation->setEmetteur(null);
            }
        }
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Retourne la version active a une date donnee (ou aujourd'hui)
     */
    public function getVersionActive(?\DateTimeInterface $date = null): ?EmetteurVersion
    {
        $date = $date ?? new \DateTime();

        foreach ($this->versions as $version) {
            if ($version->getDateEffet() <= $date &&
                ($version->getDateFin() === null || $version->getDateFin() >= $date)) {
                return $version;
            }
        }

        return null;
    }

    /**
     * Retourne la CGV par defaut pour cet emetteur
     */
    public function getCgvDefaut(): ?Cgv
    {
        foreach ($this->cgvAssociations as $association) {
            if ($association->isParDefaut()) {
                return $association->getCgv();
            }
        }
        return null;
    }

    /**
     * Retourne la raison sociale de la version active (pour affichage rapide)
     */
    public function getRaisonSociale(): ?string
    {
        $version = $this->getVersionActive();
        return $version?->getRaisonSociale();
    }

    public function __toString(): string
    {
        return $this->nom ?? $this->code ?? '';
    }
}
