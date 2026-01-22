<?php

namespace App\Entity;

use App\Repository\FactureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FactureRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Facture
{
    public const STATUT_BROUILLON = 'brouillon';
    public const STATUT_VALIDEE = 'validee';
    public const STATUT_ENVOYEE = 'envoyee';
    public const STATUT_PAYEE = 'payee';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true, nullable: true)]
    private ?string $numero = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Contrat $contrat = null;

    // Snapshot client (copie au moment de la creation)
    #[ORM\Column(length: 20)]
    private ?string $clientCode = null;

    #[ORM\Column(length: 255)]
    private ?string $clientRaisonSociale = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $clientAdresse = null;

    #[ORM\Column(length: 9, nullable: true)]
    private ?string $clientSiren = null;

    // Snapshot emetteur (copie au moment de la creation)
    #[ORM\Column(length: 255)]
    private ?string $emetteurRaisonSociale = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $emetteurAdresse = null;

    #[ORM\Column(length: 9)]
    private ?string $emetteurSiren = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $emetteurTva = null;

    #[ORM\Column(length: 34, nullable: true)]
    private ?string $emetteurIban = null;

    #[ORM\Column(length: 11, nullable: true)]
    private ?string $emetteurBic = null;

    // Dates
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date de facture est obligatoire')]
    private ?\DateTimeInterface $dateFacture = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date d'echeance est obligatoire")]
    private ?\DateTimeInterface $dateEcheance = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $periodeDebut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $periodeFin = null;

    // Montants (decimal 12,2)
    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $totalHt = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $totalTva = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $totalTtc = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'La remise doit etre entre 0 et 100')]
    private ?string $remiseGlobale = null;

    // Contenu
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $mentionsLegales = null;

    // Statut et dates de transition
    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::STATUT_BROUILLON, self::STATUT_VALIDEE, self::STATUT_ENVOYEE, self::STATUT_PAYEE])]
    private ?string $statut = self::STATUT_BROUILLON;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateValidation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateEnvoi = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $datePaiement = null;

    // Relations
    #[ORM\OneToMany(mappedBy: 'facture', targetEntity: LigneFacture::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $lignes;

    // Timestamps
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->lignes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): static
    {
        $this->numero = $numero;
        return $this;
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

    public function getClientCode(): ?string
    {
        return $this->clientCode;
    }

    public function setClientCode(string $clientCode): static
    {
        $this->clientCode = $clientCode;
        return $this;
    }

    public function getClientRaisonSociale(): ?string
    {
        return $this->clientRaisonSociale;
    }

    public function setClientRaisonSociale(string $clientRaisonSociale): static
    {
        $this->clientRaisonSociale = $clientRaisonSociale;
        return $this;
    }

    public function getClientAdresse(): ?string
    {
        return $this->clientAdresse;
    }

    public function setClientAdresse(?string $clientAdresse): static
    {
        $this->clientAdresse = $clientAdresse;
        return $this;
    }

    public function getClientSiren(): ?string
    {
        return $this->clientSiren;
    }

    public function setClientSiren(?string $clientSiren): static
    {
        $this->clientSiren = $clientSiren;
        return $this;
    }

    public function getEmetteurRaisonSociale(): ?string
    {
        return $this->emetteurRaisonSociale;
    }

    public function setEmetteurRaisonSociale(string $emetteurRaisonSociale): static
    {
        $this->emetteurRaisonSociale = $emetteurRaisonSociale;
        return $this;
    }

    public function getEmetteurAdresse(): ?string
    {
        return $this->emetteurAdresse;
    }

    public function setEmetteurAdresse(string $emetteurAdresse): static
    {
        $this->emetteurAdresse = $emetteurAdresse;
        return $this;
    }

    public function getEmetteurSiren(): ?string
    {
        return $this->emetteurSiren;
    }

    public function setEmetteurSiren(string $emetteurSiren): static
    {
        $this->emetteurSiren = $emetteurSiren;
        return $this;
    }

    public function getEmetteurTva(): ?string
    {
        return $this->emetteurTva;
    }

    public function setEmetteurTva(?string $emetteurTva): static
    {
        $this->emetteurTva = $emetteurTva;
        return $this;
    }

    public function getEmetteurIban(): ?string
    {
        return $this->emetteurIban;
    }

    public function setEmetteurIban(?string $emetteurIban): static
    {
        $this->emetteurIban = $emetteurIban;
        return $this;
    }

    public function getEmetteurBic(): ?string
    {
        return $this->emetteurBic;
    }

    public function setEmetteurBic(?string $emetteurBic): static
    {
        $this->emetteurBic = $emetteurBic;
        return $this;
    }

    public function getDateFacture(): ?\DateTimeInterface
    {
        return $this->dateFacture;
    }

    public function setDateFacture(\DateTimeInterface $dateFacture): static
    {
        $this->dateFacture = $dateFacture;
        return $this;
    }

    public function getDateEcheance(): ?\DateTimeInterface
    {
        return $this->dateEcheance;
    }

    public function setDateEcheance(\DateTimeInterface $dateEcheance): static
    {
        $this->dateEcheance = $dateEcheance;
        return $this;
    }

    public function getPeriodeDebut(): ?\DateTimeInterface
    {
        return $this->periodeDebut;
    }

    public function setPeriodeDebut(\DateTimeInterface $periodeDebut): static
    {
        $this->periodeDebut = $periodeDebut;
        return $this;
    }

    public function getPeriodeFin(): ?\DateTimeInterface
    {
        return $this->periodeFin;
    }

    public function setPeriodeFin(\DateTimeInterface $periodeFin): static
    {
        $this->periodeFin = $periodeFin;
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

    public function getTotalTva(): ?string
    {
        return $this->totalTva;
    }

    public function setTotalTva(string $totalTva): static
    {
        $this->totalTva = $totalTva;
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

    public function getRemiseGlobale(): ?string
    {
        return $this->remiseGlobale;
    }

    public function setRemiseGlobale(?string $remiseGlobale): static
    {
        $this->remiseGlobale = $remiseGlobale;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function getMentionsLegales(): ?string
    {
        return $this->mentionsLegales;
    }

    public function setMentionsLegales(?string $mentionsLegales): static
    {
        $this->mentionsLegales = $mentionsLegales;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDateValidation(): ?\DateTimeInterface
    {
        return $this->dateValidation;
    }

    public function setDateValidation(?\DateTimeInterface $dateValidation): static
    {
        $this->dateValidation = $dateValidation;
        return $this;
    }

    public function getDateEnvoi(): ?\DateTimeInterface
    {
        return $this->dateEnvoi;
    }

    public function setDateEnvoi(?\DateTimeInterface $dateEnvoi): static
    {
        $this->dateEnvoi = $dateEnvoi;
        return $this;
    }

    public function getDatePaiement(): ?\DateTimeInterface
    {
        return $this->datePaiement;
    }

    public function setDatePaiement(?\DateTimeInterface $datePaiement): static
    {
        $this->datePaiement = $datePaiement;
        return $this;
    }

    /**
     * @return Collection<int, LigneFacture>
     */
    public function getLignes(): Collection
    {
        return $this->lignes;
    }

    public function addLigne(LigneFacture $ligne): static
    {
        if (!$this->lignes->contains($ligne)) {
            $this->lignes->add($ligne);
            $ligne->setFacture($this);
        }
        return $this;
    }

    public function removeLigne(LigneFacture $ligne): static
    {
        if ($this->lignes->removeElement($ligne)) {
            if ($ligne->getFacture() === $this) {
                $ligne->setFacture(null);
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
     * Recalcule les totaux depuis les lignes
     */
    public function recalculerTotaux(): static
    {
        $totalHt = '0.00';
        $totalTva = '0.00';

        foreach ($this->lignes as $ligne) {
            $totalHt = bcadd($totalHt, $ligne->getTotalHt(), 2);
            $totalTva = bcadd($totalTva, $ligne->getMontantTva(), 2);
        }

        // Appliquer la remise globale si presente
        if ($this->remiseGlobale !== null && bccomp($this->remiseGlobale, '0', 2) > 0) {
            $remiseHt = bcmul($totalHt, bcdiv($this->remiseGlobale, '100', 4), 2);
            $remiseTva = bcmul($totalTva, bcdiv($this->remiseGlobale, '100', 4), 2);
            $totalHt = bcsub($totalHt, $remiseHt, 2);
            $totalTva = bcsub($totalTva, $remiseTva, 2);
        }

        $this->totalHt = $totalHt;
        $this->totalTva = $totalTva;
        $this->totalTtc = bcadd($totalHt, $totalTva, 2);

        return $this;
    }

    /**
     * Retourne le libelle du statut
     */
    public function getStatutLabel(): string
    {
        return match ($this->statut) {
            self::STATUT_BROUILLON => 'Brouillon',
            self::STATUT_VALIDEE => 'Validee',
            self::STATUT_ENVOYEE => 'Envoyee',
            self::STATUT_PAYEE => 'Payee',
            default => $this->statut ?? '',
        };
    }

    /**
     * Retourne la classe CSS pour le badge du statut
     */
    public function getStatutColor(): string
    {
        return match ($this->statut) {
            self::STATUT_BROUILLON => 'bg-gray-100 text-gray-800',
            self::STATUT_VALIDEE => 'bg-blue-100 text-blue-800',
            self::STATUT_ENVOYEE => 'bg-yellow-100 text-yellow-800',
            self::STATUT_PAYEE => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Verifie si la facture peut etre modifiee
     */
    public function isEditable(): bool
    {
        return $this->statut === self::STATUT_BROUILLON;
    }

    /**
     * Formate l'IBAN emetteur pour affichage
     */
    public function getEmetteurIbanFormatted(): ?string
    {
        if (!$this->emetteurIban) {
            return null;
        }
        return implode(' ', str_split($this->emetteurIban, 4));
    }

    /**
     * Formate le SIREN emetteur pour affichage
     */
    public function getEmetteurSirenFormatted(): ?string
    {
        if (!$this->emetteurSiren) {
            return null;
        }
        return implode(' ', str_split($this->emetteurSiren, 3));
    }

    /**
     * Formate le SIREN client pour affichage
     */
    public function getClientSirenFormatted(): ?string
    {
        if (!$this->clientSiren) {
            return null;
        }
        return implode(' ', str_split($this->clientSiren, 3));
    }

    public function __toString(): string
    {
        return $this->numero ?? '';
    }
}
