<?php

namespace App\Entity;

use App\Repository\ContratEvenementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContratEvenementRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ContratEvenement
{
    public const TYPE_CREATION = 'creation';
    public const TYPE_MODIFICATION = 'modification';
    public const TYPE_RENOUVELLEMENT = 'renouvellement';
    public const TYPE_SUSPENSION = 'suspension';
    public const TYPE_RESILIATION = 'resiliation';
    public const TYPE_AJOUT_MODULE = 'ajout_module';
    public const TYPE_RETRAIT_MODULE = 'retrait_module';
    public const TYPE_CHANGEMENT_TARIF = 'changement_tarif';
    public const TYPE_AUTRE = 'autre';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'evenements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Contrat $contrat = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le type d\'evenement est obligatoire')]
    #[Assert\Choice(choices: [
        self::TYPE_CREATION,
        self::TYPE_MODIFICATION,
        self::TYPE_RENOUVELLEMENT,
        self::TYPE_SUSPENSION,
        self::TYPE_RESILIATION,
        self::TYPE_AJOUT_MODULE,
        self::TYPE_RETRAIT_MODULE,
        self::TYPE_CHANGEMENT_TARIF,
        self::TYPE_AUTRE
    ])]
    private ?string $type = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'La description est obligatoire')]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateEffet = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $auteur = null;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getDateEffet(): ?\DateTimeInterface
    {
        return $this->dateEffet;
    }

    public function setDateEffet(?\DateTimeInterface $dateEffet): static
    {
        $this->dateEffet = $dateEffet;
        return $this;
    }

    public function getAuteur(): ?string
    {
        return $this->auteur;
    }

    public function setAuteur(?string $auteur): static
    {
        $this->auteur = $auteur;
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
     * Retourne le libelle du type d'evenement
     */
    public function getTypeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_CREATION => 'Creation',
            self::TYPE_MODIFICATION => 'Modification',
            self::TYPE_RENOUVELLEMENT => 'Renouvellement',
            self::TYPE_SUSPENSION => 'Suspension',
            self::TYPE_RESILIATION => 'Resiliation',
            self::TYPE_AJOUT_MODULE => 'Ajout de module',
            self::TYPE_RETRAIT_MODULE => 'Retrait de module',
            self::TYPE_CHANGEMENT_TARIF => 'Changement de tarif',
            self::TYPE_AUTRE => 'Autre',
            default => $this->type ?? '',
        };
    }

    /**
     * Retourne la couleur associee au type
     */
    public function getTypeColor(): string
    {
        return match ($this->type) {
            self::TYPE_CREATION => 'green',
            self::TYPE_MODIFICATION => 'blue',
            self::TYPE_RENOUVELLEMENT => 'indigo',
            self::TYPE_SUSPENSION => 'yellow',
            self::TYPE_RESILIATION => 'red',
            self::TYPE_AJOUT_MODULE => 'teal',
            self::TYPE_RETRAIT_MODULE => 'orange',
            self::TYPE_CHANGEMENT_TARIF => 'purple',
            default => 'gray',
        };
    }
}
