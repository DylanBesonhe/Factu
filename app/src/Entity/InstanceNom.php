<?php

namespace App\Entity;

use App\Repository\InstanceNomRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InstanceNomRepository::class)]
#[ORM\HasLifecycleCallbacks]
class InstanceNom
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'historiqueNoms')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Instance $instance = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'ancien nom est obligatoire')]
    private ?string $ancienNom = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateChangement = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAncienNom(): ?string
    {
        return $this->ancienNom;
    }

    public function setAncienNom(string $ancienNom): static
    {
        $this->ancienNom = $ancienNom;
        return $this;
    }

    public function getDateChangement(): ?\DateTimeImmutable
    {
        return $this->dateChangement;
    }

    public function setDateChangement(\DateTimeImmutable $dateChangement): static
    {
        $this->dateChangement = $dateChangement;
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
        if ($this->dateChangement === null) {
            $this->dateChangement = new \DateTimeImmutable();
        }
    }
}
