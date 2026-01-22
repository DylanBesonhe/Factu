<?php

namespace App\Entity;

use App\Repository\EmetteurCgvRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmetteurCgvRepository::class)]
#[ORM\HasLifecycleCallbacks]
class EmetteurCgv
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'cgvAssociations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Emetteur $emetteur = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cgv $cgv = null;

    #[ORM\Column]
    private bool $parDefaut = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCgv(): ?Cgv
    {
        return $this->cgv;
    }

    public function setCgv(?Cgv $cgv): static
    {
        $this->cgv = $cgv;
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
