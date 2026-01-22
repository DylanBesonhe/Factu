<?php

namespace App\Entity;

use App\Repository\ContratRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContratRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Contrat
{
    public const PERIODICITE_MENSUELLE = 'mensuelle';
    public const PERIODICITE_TRIMESTRIELLE = 'trimestrielle';
    public const PERIODICITE_ANNUELLE = 'annuelle';

    public const STATUT_ACTIF = 'actif';
    public const STATUT_SUSPENDU = 'suspendu';
    public const STATUT_RESILIE = 'resilie';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: 'Le numero de contrat est obligatoire')]
    private ?string $numero = null;

    #[ORM\ManyToOne(inversedBy: 'contrats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[ORM\ManyToOne(inversedBy: 'contrats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Instance $instance = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Emetteur $emetteur = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date de signature est obligatoire')]
    private ?\DateTimeInterface $dateSignature = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date anniversaire est obligatoire')]
    private ?\DateTimeInterface $dateAnniversaire = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::PERIODICITE_MENSUELLE, self::PERIODICITE_TRIMESTRIELLE, self::PERIODICITE_ANNUELLE])]
    private ?string $periodicite = self::PERIODICITE_MENSUELLE;

    #[ORM\Column]
    private bool $factureParticuliere = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaireFacture = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $remiseGlobale = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::STATUT_ACTIF, self::STATUT_SUSPENDU, self::STATUT_RESILIE])]
    private ?string $statut = self::STATUT_ACTIF;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\OneToMany(mappedBy: 'contrat', targetEntity: LigneContrat::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $lignes;

    #[ORM\OneToMany(mappedBy: 'contrat', targetEntity: ContratEvenement::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $evenements;

    #[ORM\OneToMany(mappedBy: 'contrat', targetEntity: ContratFichier::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $fichiers;

    #[ORM\OneToMany(mappedBy: 'contrat', targetEntity: ContratCgv::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['dateDebut' => 'DESC'])]
    private Collection $cgvAssociations;

    #[ORM\OneToMany(mappedBy: 'contrat', targetEntity: HistoriqueLicence::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['dateEffet' => 'DESC'])]
    private Collection $historiqueLicences;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->lignes = new ArrayCollection();
        $this->evenements = new ArrayCollection();
        $this->fichiers = new ArrayCollection();
        $this->cgvAssociations = new ArrayCollection();
        $this->historiqueLicences = new ArrayCollection();
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

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;
        return $this;
    }

    public function getInstance(): ?Instance
    {
        return $this->instance;
    }

    public function setInstance(?Instance $instance): static
    {
        $this->instance = $instance;
        return $this;
    }

    public function getEmetteur(): ?Emetteur
    {
        return $this->emetteur;
    }

    public function setEmetteur(?Emetteur $emetteur): static
    {
        $this->emetteur = $emetteur;
        return $this;
    }

    public function getDateSignature(): ?\DateTimeInterface
    {
        return $this->dateSignature;
    }

    public function setDateSignature(\DateTimeInterface $dateSignature): static
    {
        $this->dateSignature = $dateSignature;
        return $this;
    }

    public function getDateAnniversaire(): ?\DateTimeInterface
    {
        return $this->dateAnniversaire;
    }

    public function setDateAnniversaire(\DateTimeInterface $dateAnniversaire): static
    {
        $this->dateAnniversaire = $dateAnniversaire;
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

    public function getPeriodicite(): ?string
    {
        return $this->periodicite;
    }

    public function setPeriodicite(string $periodicite): static
    {
        $this->periodicite = $periodicite;
        return $this;
    }

    public function isFactureParticuliere(): bool
    {
        return $this->factureParticuliere;
    }

    public function setFactureParticuliere(bool $factureParticuliere): static
    {
        $this->factureParticuliere = $factureParticuliere;
        return $this;
    }

    public function getCommentaireFacture(): ?string
    {
        return $this->commentaireFacture;
    }

    public function setCommentaireFacture(?string $commentaireFacture): static
    {
        $this->commentaireFacture = $commentaireFacture;
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

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * @return Collection<int, LigneContrat>
     */
    public function getLignes(): Collection
    {
        return $this->lignes;
    }

    public function addLigne(LigneContrat $ligne): static
    {
        if (!$this->lignes->contains($ligne)) {
            $this->lignes->add($ligne);
            $ligne->setContrat($this);
        }
        return $this;
    }

    public function removeLigne(LigneContrat $ligne): static
    {
        if ($this->lignes->removeElement($ligne)) {
            if ($ligne->getContrat() === $this) {
                $ligne->setContrat(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, ContratEvenement>
     */
    public function getEvenements(): Collection
    {
        return $this->evenements;
    }

    public function addEvenement(ContratEvenement $evenement): static
    {
        if (!$this->evenements->contains($evenement)) {
            $this->evenements->add($evenement);
            $evenement->setContrat($this);
        }
        return $this;
    }

    public function removeEvenement(ContratEvenement $evenement): static
    {
        if ($this->evenements->removeElement($evenement)) {
            if ($evenement->getContrat() === $this) {
                $evenement->setContrat(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, ContratFichier>
     */
    public function getFichiers(): Collection
    {
        return $this->fichiers;
    }

    public function addFichier(ContratFichier $fichier): static
    {
        if (!$this->fichiers->contains($fichier)) {
            $this->fichiers->add($fichier);
            $fichier->setContrat($this);
        }
        return $this;
    }

    public function removeFichier(ContratFichier $fichier): static
    {
        if ($this->fichiers->removeElement($fichier)) {
            if ($fichier->getContrat() === $this) {
                $fichier->setContrat(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, ContratCgv>
     */
    public function getCgvAssociations(): Collection
    {
        return $this->cgvAssociations;
    }

    public function addCgvAssociation(ContratCgv $cgvAssociation): static
    {
        if (!$this->cgvAssociations->contains($cgvAssociation)) {
            $this->cgvAssociations->add($cgvAssociation);
            $cgvAssociation->setContrat($this);
        }
        return $this;
    }

    public function removeCgvAssociation(ContratCgv $cgvAssociation): static
    {
        if ($this->cgvAssociations->removeElement($cgvAssociation)) {
            if ($cgvAssociation->getContrat() === $this) {
                $cgvAssociation->setContrat(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, HistoriqueLicence>
     */
    public function getHistoriqueLicences(): Collection
    {
        return $this->historiqueLicences;
    }

    public function addHistoriqueLicence(HistoriqueLicence $historiqueLicence): static
    {
        if (!$this->historiqueLicences->contains($historiqueLicence)) {
            $this->historiqueLicences->add($historiqueLicence);
            $historiqueLicence->setContrat($this);
        }
        return $this;
    }

    public function removeHistoriqueLicence(HistoriqueLicence $historiqueLicence): static
    {
        if ($this->historiqueLicences->removeElement($historiqueLicence)) {
            if ($historiqueLicence->getContrat() === $this) {
                $historiqueLicence->setContrat(null);
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
     * Calcule le total HT du contrat (somme des lignes)
     */
    public function getTotalHt(): string
    {
        $total = '0.00';
        foreach ($this->lignes as $ligne) {
            $total = bcadd($total, $ligne->getTotalHt(), 2);
        }

        if ($this->remiseGlobale !== null && $this->remiseGlobale > 0) {
            $remise = bcmul($total, bcdiv($this->remiseGlobale, '100', 4), 2);
            $total = bcsub($total, $remise, 2);
        }

        return $total;
    }

    /**
     * Calcule le total TVA du contrat
     */
    public function getTotalTva(): string
    {
        $totalTva = '0.00';
        foreach ($this->lignes as $ligne) {
            $totalTva = bcadd($totalTva, $ligne->getMontantTva(), 2);
        }

        if ($this->remiseGlobale !== null && $this->remiseGlobale > 0) {
            $remise = bcmul($totalTva, bcdiv($this->remiseGlobale, '100', 4), 2);
            $totalTva = bcsub($totalTva, $remise, 2);
        }

        return $totalTva;
    }

    /**
     * Calcule le total TTC du contrat
     */
    public function getTotalTtc(): string
    {
        return bcadd($this->getTotalHt(), $this->getTotalTva(), 2);
    }

    /**
     * Retourne le nombre total de licences (somme des quantites)
     */
    public function getNbLicences(): int
    {
        $total = 0;
        foreach ($this->lignes as $ligne) {
            $total += $ligne->getQuantite();
        }
        return $total;
    }

    /**
     * Verifie si le contrat est actif
     */
    public function isActif(): bool
    {
        if ($this->statut !== self::STATUT_ACTIF) {
            return false;
        }

        if ($this->dateFin !== null && $this->dateFin < new \DateTime()) {
            return false;
        }

        return true;
    }

    /**
     * Retourne la CGV active pour ce contrat
     */
    public function getCgvActive(): ?Cgv
    {
        $now = new \DateTime();
        foreach ($this->cgvAssociations as $association) {
            if ($association->getDateDebut() <= $now &&
                ($association->getDateFin() === null || $association->getDateFin() >= $now)) {
                return $association->getCgv();
            }
        }
        return null;
    }

    /**
     * Retourne le libelle de la periodicite
     */
    public function getPeriodiciteLabel(): string
    {
        return match ($this->periodicite) {
            self::PERIODICITE_MENSUELLE => 'Mensuelle',
            self::PERIODICITE_TRIMESTRIELLE => 'Trimestrielle',
            self::PERIODICITE_ANNUELLE => 'Annuelle',
            default => $this->periodicite ?? '',
        };
    }

    /**
     * Retourne le libelle du statut
     */
    public function getStatutLabel(): string
    {
        return match ($this->statut) {
            self::STATUT_ACTIF => 'Actif',
            self::STATUT_SUSPENDU => 'Suspendu',
            self::STATUT_RESILIE => 'Resilie',
            default => $this->statut ?? '',
        };
    }

    public function __toString(): string
    {
        return $this->numero ?? '';
    }
}
