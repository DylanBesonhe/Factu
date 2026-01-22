<?php

namespace App\Entity;

use App\Repository\HistoriqueLicenceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HistoriqueLicenceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class HistoriqueLicence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'historiqueLicences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Contrat $contrat = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le nombre de licences est obligatoire')]
    #[Assert\PositiveOrZero(message: 'Le nombre de licences doit etre positif ou nul')]
    private ?int $nbLicences = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date d\'effet est obligatoire')]
    private ?\DateTimeInterface $dateEffet = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $source = null;

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

    public function getNbLicences(): ?int
    {
        return $this->nbLicences;
    }

    public function setNbLicences(int $nbLicences): static
    {
        $this->nbLicences = $nbLicences;
        return $this;
    }

    public function getDateEffet(): ?\DateTimeInterface
    {
        return $this->dateEffet;
    }

    public function setDateEffet(\DateTimeInterface $dateEffet): static
    {
        $this->dateEffet = $dateEffet;
        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): static
    {
        $this->source = $source;
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
}
