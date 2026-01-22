<?php

namespace App\Entity;

use App\Repository\LigneContratRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LigneContratRepository::class)]
#[ORM\HasLifecycleCallbacks]
class LigneContrat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lignes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Contrat $contrat = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Module $module = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La quantite est obligatoire')]
    #[Assert\Positive(message: 'La quantite doit etre positive')]
    private ?int $quantite = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Le prix unitaire est obligatoire')]
    #[Assert\PositiveOrZero(message: 'Le prix unitaire doit etre positif ou nul')]
    private ?string $prixUnitaire = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'La remise doit etre entre 0 et 100')]
    private ?string $remise = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    #[Assert\NotBlank(message: 'Le taux de TVA est obligatoire')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux de TVA doit etre entre 0 et 100')]
    private ?string $tauxTva = '20.00';

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

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

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $module): static
    {
        $this->module = $module;

        if ($module !== null && $this->prixUnitaire === null) {
            $this->prixUnitaire = $module->getPrixDefaut();
        }
        if ($module !== null && $this->tauxTva === null) {
            $this->tauxTva = $module->getTauxTva();
        }

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getPrixUnitaire(): ?string
    {
        return $this->prixUnitaire;
    }

    public function setPrixUnitaire(string $prixUnitaire): static
    {
        $this->prixUnitaire = $prixUnitaire;
        return $this;
    }

    public function getRemise(): ?string
    {
        return $this->remise;
    }

    public function setRemise(?string $remise): static
    {
        $this->remise = $remise;
        return $this;
    }

    public function getTauxTva(): ?string
    {
        return $this->tauxTva;
    }

    public function setTauxTva(string $tauxTva): static
    {
        $this->tauxTva = $tauxTva;
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
     * Calcule le total HT brut (avant remise)
     */
    public function getTotalBrutHt(): string
    {
        return bcmul($this->quantite, $this->prixUnitaire, 2);
    }

    /**
     * Calcule le montant de la remise
     */
    public function getMontantRemise(): string
    {
        if ($this->remise === null || $this->remise <= 0) {
            return '0.00';
        }

        $brut = $this->getTotalBrutHt();
        return bcmul($brut, bcdiv($this->remise, '100', 4), 2);
    }

    /**
     * Calcule le total HT (apres remise)
     */
    public function getTotalHt(): string
    {
        $brut = $this->getTotalBrutHt();
        $remise = $this->getMontantRemise();
        return bcsub($brut, $remise, 2);
    }

    /**
     * Calcule le montant de TVA
     */
    public function getMontantTva(): string
    {
        $totalHt = $this->getTotalHt();
        return bcmul($totalHt, bcdiv($this->tauxTva, '100', 4), 2);
    }

    /**
     * Calcule le total TTC
     */
    public function getTotalTtc(): string
    {
        return bcadd($this->getTotalHt(), $this->getMontantTva(), 2);
    }

    /**
     * Retourne le nom du module (pour affichage)
     */
    public function getModuleNom(): ?string
    {
        return $this->module?->getNom();
    }
}
