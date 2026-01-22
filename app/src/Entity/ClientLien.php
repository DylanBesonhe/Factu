<?php

namespace App\Entity;

use App\Repository\ClientLienRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClientLienRepository::class)]
#[ORM\Table(name: 'client_lien')]
#[ORM\UniqueConstraint(name: 'unique_client_lien', columns: ['client_source_id', 'client_cible_id'])]
#[ORM\HasLifecycleCallbacks]
class ClientLien
{
    public const TYPE_FILIALE = 'filiale';
    public const TYPE_GROUPE = 'groupe';
    public const TYPE_PARTENAIRE = 'partenaire';

    public const TYPES = [
        'Filiale' => self::TYPE_FILIALE,
        'Groupe' => self::TYPE_GROUPE,
        'Partenaire' => self::TYPE_PARTENAIRE,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'liensSource')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $clientSource = null;

    #[ORM\ManyToOne(inversedBy: 'liensCible')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $clientCible = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le type de lien est obligatoire')]
    #[Assert\Choice(choices: [self::TYPE_FILIALE, self::TYPE_GROUPE, self::TYPE_PARTENAIRE], message: 'Type de lien invalide')]
    private ?string $type = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClientSource(): ?Client
    {
        return $this->clientSource;
    }

    public function setClientSource(?Client $clientSource): static
    {
        $this->clientSource = $clientSource;
        return $this;
    }

    public function getClientCible(): ?Client
    {
        return $this->clientCible;
    }

    public function setClientCible(?Client $clientCible): static
    {
        $this->clientCible = $clientCible;
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

    public function getTypeLabel(): string
    {
        return array_search($this->type, self::TYPES) ?: $this->type;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function __toString(): string
    {
        return $this->getTypeLabel() . ' - ' . ($this->clientCible?->getRaisonSociale() ?? '');
    }
}
