<?php

namespace App\Entity;

use App\Repository\ParametreFacturationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParametreFacturationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ParametreFacturation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'parametreFacturation')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Emetteur $emetteur = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le format de numero est obligatoire')]
    private ?string $formatNumero = 'FA-{YYYY}-{SEQ:5}';

    #[ORM\Column]
    #[Assert\Positive(message: 'Le prochain numero doit etre positif')]
    private int $prochainNumero = 1;

    #[ORM\Column]
    #[Assert\Positive(message: "Le delai d'echeance doit etre positif")]
    private int $delaiEcheance = 30;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mentionsLegales = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emailObjet = 'Facture {NUMERO} - {CLIENT}';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $emailCorps = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

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

    public function getFormatNumero(): ?string
    {
        return $this->formatNumero;
    }

    public function setFormatNumero(string $formatNumero): static
    {
        $this->formatNumero = $formatNumero;
        return $this;
    }

    public function getProchainNumero(): int
    {
        return $this->prochainNumero;
    }

    public function setProchainNumero(int $prochainNumero): static
    {
        $this->prochainNumero = $prochainNumero;
        return $this;
    }

    public function incrementProchainNumero(): static
    {
        $this->prochainNumero++;
        return $this;
    }

    public function getDelaiEcheance(): int
    {
        return $this->delaiEcheance;
    }

    public function setDelaiEcheance(int $delaiEcheance): static
    {
        $this->delaiEcheance = $delaiEcheance;
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

    public function getEmailObjet(): ?string
    {
        return $this->emailObjet;
    }

    public function setEmailObjet(?string $emailObjet): static
    {
        $this->emailObjet = $emailObjet;
        return $this;
    }

    public function getEmailCorps(): ?string
    {
        return $this->emailCorps;
    }

    public function setEmailCorps(?string $emailCorps): static
    {
        $this->emailCorps = $emailCorps;
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
    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Genere un numero de facture selon le format configure
     * Variables supportees:
     * - {YYYY}: Annee sur 4 chiffres
     * - {YY}: Annee sur 2 chiffres
     * - {MM}: Mois sur 2 chiffres
     * - {SEQ:N}: Sequence sur N chiffres
     * - {CODE}: Code de l'emetteur
     * - {SIREN}: SIREN de l'emetteur
     */
    public function genererNumero(): string
    {
        $numero = $this->formatNumero;

        // Variables de date
        $numero = str_replace('{YYYY}', date('Y'), $numero);
        $numero = str_replace('{YY}', date('y'), $numero);
        $numero = str_replace('{MM}', date('m'), $numero);

        // Variables de l'emetteur
        if ($this->emetteur) {
            $numero = str_replace('{CODE}', $this->emetteur->getCode() ?? '', $numero);
            $version = $this->emetteur->getVersionActive();
            if ($version) {
                $numero = str_replace('{SIREN}', $version->getSiren() ?? '', $numero);
            } else {
                $numero = str_replace('{SIREN}', '', $numero);
            }
        } else {
            $numero = str_replace('{CODE}', '', $numero);
            $numero = str_replace('{SIREN}', '', $numero);
        }

        // Sequence
        $numero = preg_replace_callback('/\{SEQ:(\d+)\}/', function($matches) {
            return str_pad($this->prochainNumero, (int)$matches[1], '0', STR_PAD_LEFT);
        }, $numero);

        return $numero;
    }

    /**
     * Genere un apercu du prochain numero sans incrementer
     */
    public function getApercuNumero(): string
    {
        return $this->genererNumero();
    }
}
