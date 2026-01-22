<?php

namespace App\Entity;

use App\Repository\EmetteurVersionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmetteurVersionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class EmetteurVersion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'versions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Emetteur $emetteur = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La raison sociale est obligatoire')]
    private ?string $raisonSociale = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $formeJuridique = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
    private ?string $capital = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "L'adresse est obligatoire")]
    private ?string $adresse = null;

    #[ORM\Column(length: 9)]
    #[Assert\NotBlank(message: 'Le SIREN est obligatoire')]
    #[Assert\Length(exactly: 9, exactMessage: 'Le SIREN doit contenir exactement 9 chiffres')]
    #[Assert\Regex(pattern: '/^\d{9}$/', message: 'Le SIREN doit contenir uniquement des chiffres')]
    private ?string $siren = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $tva = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: "L'email est obligatoire")]
    #[Assert\Email(message: "L'email n'est pas valide")]
    private ?string $email = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 34, nullable: true)]
    private ?string $iban = null;

    #[ORM\Column(length: 11, nullable: true)]
    private ?string $bic = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date d'effet est obligatoire")]
    private ?\DateTimeInterface $dateEffet = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateFin = null;

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

    public function getRaisonSociale(): ?string
    {
        return $this->raisonSociale;
    }

    public function setRaisonSociale(string $raisonSociale): static
    {
        $this->raisonSociale = $raisonSociale;
        return $this;
    }

    public function getFormeJuridique(): ?string
    {
        return $this->formeJuridique;
    }

    public function setFormeJuridique(?string $formeJuridique): static
    {
        $this->formeJuridique = $formeJuridique;
        return $this;
    }

    public function getCapital(): ?string
    {
        return $this->capital;
    }

    public function setCapital(?string $capital): static
    {
        $this->capital = $capital;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getSiren(): ?string
    {
        return $this->siren;
    }

    public function setSiren(string $siren): static
    {
        $this->siren = preg_replace('/\s/', '', $siren);
        return $this;
    }

    public function getTva(): ?string
    {
        return $this->tva;
    }

    public function setTva(?string $tva): static
    {
        $this->tva = $tva;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
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

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function setIban(?string $iban): static
    {
        $this->iban = $iban ? strtoupper(preg_replace('/\s/', '', $iban)) : null;
        return $this;
    }

    public function getBic(): ?string
    {
        return $this->bic;
    }

    public function setBic(?string $bic): static
    {
        $this->bic = $bic ? strtoupper($bic) : null;
        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;
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

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;
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

    public function getIbanFormatted(): ?string
    {
        if (!$this->iban) {
            return null;
        }
        return implode(' ', str_split($this->iban, 4));
    }

    public function getSirenFormatted(): ?string
    {
        if (!$this->siren) {
            return null;
        }
        return implode(' ', str_split($this->siren, 3));
    }

    public function getIbanLast4(): ?string
    {
        if (!$this->iban) {
            return null;
        }
        return '...' . substr($this->iban, -4);
    }

    public function isActive(?\DateTimeInterface $date = null): bool
    {
        $date = $date ?? new \DateTime();

        if ($this->dateEffet > $date) {
            return false;
        }

        if ($this->dateFin !== null && $this->dateFin < $date) {
            return false;
        }

        return true;
    }
}
