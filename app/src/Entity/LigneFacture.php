<?php

namespace App\Entity;

use App\Repository\LigneFactureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LigneFactureRepository::class)]
#[ORM\HasLifecycleCallbacks]
class LigneFacture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lignes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Facture $facture = null;

    // Donnees (snapshot de LigneContrat)
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La designation est obligatoire')]
    private ?string $designation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

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

    // Montants pre-calcules (decimal 12,2)
    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $totalHt = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $montantTva = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $totalTtc = '0.00';

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function setFacture(?Facture $facture): static
    {
        $this->facture = $facture;
        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): static
    {
        $this->designation = $designation;
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

    public function getTotalHt(): ?string
    {
        return $this->totalHt;
    }

    public function setTotalHt(string $totalHt): static
    {
        $this->totalHt = $totalHt;
        return $this;
    }

    public function getMontantTva(): ?string
    {
        return $this->montantTva;
    }

    public function setMontantTva(string $montantTva): static
    {
        $this->montantTva = $montantTva;
        return $this;
    }

    public function getTotalTtc(): ?string
    {
        return $this->totalTtc;
    }

    public function setTotalTtc(string $totalTtc): static
    {
        $this->totalTtc = $totalTtc;
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
        $this->calculerTotaux();
    }

    /**
     * Calcule le total HT brut (avant remise)
     */
    public function getTotalBrutHt(): string
    {
        return bcmul((string)$this->quantite, $this->prixUnitaire ?? '0', 2);
    }

    /**
     * Calcule le montant de la remise
     */
    public function getMontantRemise(): string
    {
        if ($this->remise === null || bccomp($this->remise, '0', 2) <= 0) {
            return '0.00';
        }

        $brut = $this->getTotalBrutHt();
        return bcmul($brut, bcdiv($this->remise, '100', 4), 2);
    }

    /**
     * Calcule et stocke les totaux (appele avant persist)
     */
    public function calculerTotaux(): static
    {
        // Total HT apres remise
        $brut = $this->getTotalBrutHt();
        $remise = $this->getMontantRemise();
        $this->totalHt = bcsub($brut, $remise, 2);

        // Montant TVA
        $this->montantTva = bcmul($this->totalHt, bcdiv($this->tauxTva ?? '0', '100', 4), 2);

        // Total TTC
        $this->totalTtc = bcadd($this->totalHt, $this->montantTva, 2);

        return $this;
    }
}
