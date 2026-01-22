<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use App\Validator as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[ORM\Table(name: 'client')]
#[ORM\HasLifecycleCallbacks]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    #[Assert\NotBlank(message: 'Le code est obligatoire')]
    #[Assert\Length(max: 20)]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La raison sociale est obligatoire')]
    #[Assert\Length(max: 255)]
    private ?string $raisonSociale = null;

    #[ORM\Column(length: 9, nullable: true)]
    #[AppAssert\Siren]
    private ?string $siren = null;

    #[ORM\Column(length: 34, nullable: true)]
    #[AppAssert\Iban]
    private ?string $iban = null;

    #[ORM\Column(length: 11, nullable: true)]
    #[Assert\Length(max: 11)]
    private ?string $bic = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email(message: 'Email invalide')]
    private ?string $email = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Length(max: 20)]
    private ?string $telephone = null;

    #[ORM\Column]
    private bool $actif = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: Contact::class, mappedBy: 'client', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $contacts;

    #[ORM\OneToMany(targetEntity: ClientNote::class, mappedBy: 'client', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $notes;

    #[ORM\OneToMany(targetEntity: ClientLien::class, mappedBy: 'clientSource', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $liensSource;

    #[ORM\OneToMany(targetEntity: ClientLien::class, mappedBy: 'clientCible')]
    private Collection $liensCible;

    #[ORM\OneToMany(targetEntity: Contrat::class, mappedBy: 'client')]
    private Collection $contrats;

    public function __construct()
    {
        $this->contacts = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->liensSource = new ArrayCollection();
        $this->liensCible = new ArrayCollection();
        $this->contrats = new ArrayCollection();
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;
        return $this;
    }

    public function getRaisonSociale(): ?string
    {
        return $this->raisonSociale;
    }

    public function setRaisonSociale(string $raisonSociale): static
    {
        $this->raisonSociale = $raisonSociale;
        return $this;
    }

    public function getSiren(): ?string
    {
        return $this->siren;
    }

    public function setSiren(?string $siren): static
    {
        $this->siren = $siren;
        return $this;
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function setIban(?string $iban): static
    {
        $this->iban = $iban;
        return $this;
    }

    public function getBic(): ?string
    {
        return $this->bic;
    }

    public function setBic(?string $bic): static
    {
        $this->bic = $bic;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return Collection<int, Contact>
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact): static
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts->add($contact);
            $contact->setClient($this);
        }
        return $this;
    }

    public function removeContact(Contact $contact): static
    {
        if ($this->contacts->removeElement($contact)) {
            if ($contact->getClient() === $this) {
                $contact->setClient(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, ClientNote>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(ClientNote $note): static
    {
        if (!$this->notes->contains($note)) {
            $this->notes->add($note);
            $note->setClient($this);
        }
        return $this;
    }

    public function removeNote(ClientNote $note): static
    {
        if ($this->notes->removeElement($note)) {
            if ($note->getClient() === $this) {
                $note->setClient(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, ClientLien>
     */
    public function getLiensSource(): Collection
    {
        return $this->liensSource;
    }

    public function addLienSource(ClientLien $lien): static
    {
        if (!$this->liensSource->contains($lien)) {
            $this->liensSource->add($lien);
            $lien->setClientSource($this);
        }
        return $this;
    }

    public function removeLienSource(ClientLien $lien): static
    {
        if ($this->liensSource->removeElement($lien)) {
            if ($lien->getClientSource() === $this) {
                $lien->setClientSource(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, ClientLien>
     */
    public function getLiensCible(): Collection
    {
        return $this->liensCible;
    }

    public function getContactPrincipal(): ?Contact
    {
        foreach ($this->contacts as $contact) {
            if ($contact->isPrincipal()) {
                return $contact;
            }
        }
        return null;
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
            $contrat->setClient($this);
        }
        return $this;
    }

    public function removeContrat(Contrat $contrat): static
    {
        if ($this->contrats->removeElement($contrat)) {
            if ($contrat->getClient() === $this) {
                $contrat->setClient(null);
            }
        }
        return $this;
    }

    /**
     * Retourne le nombre total de licences actives
     */
    public function getNbLicences(): int
    {
        $total = 0;
        foreach ($this->contrats as $contrat) {
            if ($contrat->isActif()) {
                $total += $contrat->getNbLicences();
            }
        }
        return $total;
    }

    /**
     * Retourne le nombre de contrats actifs
     */
    public function getNbContratsActifs(): int
    {
        $count = 0;
        foreach ($this->contrats as $contrat) {
            if ($contrat->isActif()) {
                $count++;
            }
        }
        return $count;
    }

    public function __toString(): string
    {
        return $this->code . ' - ' . $this->raisonSociale;
    }
}
