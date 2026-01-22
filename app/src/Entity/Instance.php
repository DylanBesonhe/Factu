<?php

namespace App\Entity;

use App\Repository\InstanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InstanceRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['nomActuel'], message: 'Une instance avec ce nom existe deja')]
class Instance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Le nom de l\'instance est obligatoire')]
    private ?string $nomActuel = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $url = null;

    #[ORM\Column]
    private bool $actif = true;

    #[ORM\OneToMany(mappedBy: 'instance', targetEntity: InstanceNom::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['dateChangement' => 'DESC'])]
    private Collection $historiqueNoms;

    #[ORM\OneToMany(mappedBy: 'instance', targetEntity: Contrat::class)]
    private Collection $contrats;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->historiqueNoms = new ArrayCollection();
        $this->contrats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomActuel(): ?string
    {
        return $this->nomActuel;
    }

    public function setNomActuel(string $nomActuel): static
    {
        $ancienNom = $this->nomActuel;
        $this->nomActuel = $nomActuel;

        if ($ancienNom !== null && $ancienNom !== $nomActuel) {
            $historique = new InstanceNom();
            $historique->setInstance($this);
            $historique->setAncienNom($ancienNom);
            $historique->setDateChangement(new \DateTimeImmutable());
            $this->addHistoriqueNom($historique);
        }

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;
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

    /**
     * @return Collection<int, InstanceNom>
     */
    public function getHistoriqueNoms(): Collection
    {
        return $this->historiqueNoms;
    }

    public function addHistoriqueNom(InstanceNom $historiqueNom): static
    {
        if (!$this->historiqueNoms->contains($historiqueNom)) {
            $this->historiqueNoms->add($historiqueNom);
            $historiqueNom->setInstance($this);
        }
        return $this;
    }

    public function removeHistoriqueNom(InstanceNom $historiqueNom): static
    {
        if ($this->historiqueNoms->removeElement($historiqueNom)) {
            if ($historiqueNom->getInstance() === $this) {
                $historiqueNom->setInstance(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Contrat>
     */
    public function getContrats(): Collection
    {
        return $this->contrats;
    }

    public function addContrat(Contrat $contrat): static
    {
        if (!$this->contrats->contains($contrat)) {
            $this->contrats->add($contrat);
            $contrat->setInstance($this);
        }
        return $this;
    }

    public function removeContrat(Contrat $contrat): static
    {
        if ($this->contrats->removeElement($contrat)) {
            if ($contrat->getInstance() === $this) {
                $contrat->setInstance(null);
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

    public function __toString(): string
    {
        return $this->nomActuel ?? '';
    }
}
