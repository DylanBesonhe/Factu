# Architecture Technique - Factu

**Version:** 1.1
**Date:** 2026-01-20
**Statut:** Validé

---

## 1. Vue d'ensemble

### 1.1 Contexte technique
| Élément | Valeur |
|---------|--------|
| Type | Application web interne |
| Utilisateurs | 4-5 |
| Clients | ~400 |
| Factures/mois | ~300 |
| Fichiers | PDF, CSV |
| Hébergement | VPS |

### 1.2 Architecture globale

```
┌─────────────────────────────────────────────────────────────────┐
│                         UTILISATEURS                             │
│                         (Navigateur)                             │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ HTTPS
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                           NGINX                                  │
│                    (Reverse Proxy + SSL)                         │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                         SYMFONY 7                                │
│                   (PHP 8.3 + PHP-FPM)                           │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐              │
│  │  Controllers│  │   Services  │  │   Entities  │              │
│  │  (Routes)   │  │   (Métier)  │  │  (Doctrine) │              │
│  └─────────────┘  └─────────────┘  └─────────────┘              │
│                                                                  │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐              │
│  │    Twig     │  │ Symfony UX  │  │   Webpack   │              │
│  │ (Templates) │  │(Turbo/Live) │  │  Encore     │              │
│  └─────────────┘  └─────────────┘  └─────────────┘              │
└─────────────────────────────────────────────────────────────────┘
                              │
              ┌───────────────┼───────────────┐
              │               │               │
              ▼               ▼               ▼
       ┌───────────┐   ┌───────────┐   ┌───────────┐
       │ PostgreSQL│   │  Storage  │   │   SMTP    │
       │    16     │   │  (Files)  │   │  (Email)  │
       └───────────┘   └───────────┘   └───────────┘
```

---

## 2. Stack technique

### 2.1 Choix technologiques

| Couche | Technologie | Version | Justification |
|--------|-------------|---------|---------------|
| **Langage** | PHP | 8.3+ | Performance, typage strict |
| **Framework** | Symfony | 7.x | Robuste, écosystème riche |
| **ORM** | Doctrine | 3.x | Standard Symfony, migrations |
| **Templates** | Twig | 3.x | Intégré Symfony |
| **Frontend** | Symfony UX | - | Turbo, Live Components, Stimulus |
| **Assets** | Webpack Encore | - | Build CSS/JS |
| **CSS** | Tailwind CSS | 3.x | Utilitaires, responsive |
| **UI Components** | Flowbite | - | Composants Tailwind |
| **Database** | PostgreSQL | 16+ | Robuste, JSON, fonctions |
| **PDF** | DomPDF | 2.x | Génération PDF PHP |
| **Email** | Symfony Mailer | - | Envoi SMTP intégré |
| **Validation** | Symfony Validator | - | Contraintes natives |
| **Auth** | Symfony Security | - | Authentification intégrée |
| **Cache** | Symfony Cache | - | Redis ou fichiers |
| **Serveur Web** | Nginx | - | Reverse proxy, SSL |
| **PHP Runtime** | PHP-FPM | - | Performance |

### 2.2 Bundles Symfony

| Bundle | Usage |
|--------|-------|
| `doctrine/doctrine-bundle` | ORM |
| `doctrine/doctrine-migrations-bundle` | Migrations DB |
| `symfony/security-bundle` | Authentification |
| `symfony/twig-bundle` | Templates |
| `symfony/webpack-encore-bundle` | Assets |
| `symfony/ux-turbo` | Navigation SPA-like |
| `symfony/ux-live-component` | Composants réactifs |
| `symfony/ux-chartjs` | Graphiques |
| `symfony/mailer` | Envoi emails |
| `symfony/validator` | Validation |
| `dompdf/dompdf` | Génération PDF |
| `league/csv` | Import/Export CSV |

---

## 3. Architecture détaillée

### 3.1 Structure du projet

```
factu/
├── assets/                     # Frontend (Webpack Encore)
│   ├── controllers/            # Stimulus controllers
│   ├── styles/                 # CSS / Tailwind
│   └── app.js
│
├── config/                     # Configuration Symfony
│   ├── packages/
│   ├── routes/
│   └── services.yaml
│
├── migrations/                 # Migrations Doctrine
│
├── public/                     # Document root
│   └── index.php
│
├── src/
│   ├── Controller/             # Controllers
│   │   ├── Admin/              # Paramètres
│   │   ├── Api/                # Endpoints API (si besoin)
│   │   ├── DashboardController.php
│   │   ├── ClientController.php
│   │   ├── ContratController.php
│   │   ├── LicenceController.php
│   │   └── FacturationController.php
│   │
│   ├── Entity/                 # Entités Doctrine
│   │   ├── User.php
│   │   ├── Client.php
│   │   ├── Contact.php
│   │   ├── Contrat.php
│   │   ├── LigneContrat.php
│   │   ├── Module.php
│   │   ├── Instance.php
│   │   ├── Facture.php
│   │   └── ...
│   │
│   ├── Repository/             # Repositories Doctrine
│   │   ├── ClientRepository.php
│   │   ├── ContratRepository.php
│   │   └── ...
│   │
│   ├── Service/                # Services métier
│   │   ├── Client/
│   │   │   └── ClientService.php
│   │   ├── Contrat/
│   │   │   └── ContratService.php
│   │   ├── Licence/
│   │   │   ├── ImportService.php
│   │   │   └── ReleveService.php
│   │   ├── Facturation/
│   │   │   ├── FacturationService.php
│   │   │   └── CalculFactureService.php
│   │   ├── Pdf/
│   │   │   └── PdfGeneratorService.php
│   │   ├── Email/
│   │   │   └── FactureMailerService.php
│   │   ├── Validation/
│   │   │   ├── SirenValidator.php
│   │   │   └── IbanValidator.php
│   │   └── Export/
│   │       └── CsvExportService.php
│   │
│   ├── Form/                   # Formulaires
│   │   ├── ClientType.php
│   │   ├── ContratType.php
│   │   └── ...
│   │
│   ├── Twig/                   # Extensions Twig
│   │   └── Components/         # Twig Components
│   │       ├── Alert.php
│   │       ├── Badge.php
│   │       └── ...
│   │
│   ├── DataFixtures/           # Fixtures (dev)
│   │
│   └── EventSubscriber/        # Event listeners
│
├── templates/                  # Templates Twig
│   ├── base.html.twig
│   ├── components/             # Composants réutilisables
│   │   ├── _sidebar.html.twig
│   │   ├── _header.html.twig
│   │   ├── _table.html.twig
│   │   ├── _pagination.html.twig
│   │   └── _modal.html.twig
│   ├── dashboard/
│   │   └── index.html.twig
│   ├── client/
│   │   ├── index.html.twig
│   │   ├── show.html.twig
│   │   ├── _form.html.twig
│   │   └── _contacts.html.twig
│   ├── contrat/
│   │   ├── index.html.twig
│   │   ├── show.html.twig
│   │   └── _form.html.twig
│   ├── licence/
│   │   ├── import.html.twig
│   │   ├── releves.html.twig
│   │   └── mapping.html.twig
│   ├── facturation/
│   │   ├── workflow.html.twig
│   │   ├── liste.html.twig
│   │   └── _facture_card.html.twig
│   ├── admin/
│   │   ├── emetteur.html.twig
│   │   ├── modules.html.twig
│   │   ├── cgv.html.twig
│   │   └── users.html.twig
│   └── pdf/
│       └── facture.html.twig
│
├── storage/                    # Fichiers uploadés
│   ├── contrats/
│   ├── factures/
│   ├── imports/
│   └── cgv/
│
├── tests/                      # Tests
│   ├── Unit/
│   └── Functional/
│
├── var/                        # Cache, logs
│
├── vendor/                     # Dépendances
│
├── .env                        # Variables d'environnement
├── composer.json
├── package.json
├── tailwind.config.js
└── webpack.config.js
```

### 3.2 Architecture MVC Symfony

```
┌─────────────────────────────────────────────────────────────────┐
│                        REQUÊTE HTTP                              │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                         CONTROLLER                               │
│  - Validation des entrées                                        │
│  - Appel des services                                           │
│  - Retour de la réponse (Twig ou JSON)                          │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                          SERVICES                                │
│  - Logique métier                                               │
│  - Orchestration                                                │
│  - Appel aux repositories                                       │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                        REPOSITORIES                              │
│  - Requêtes Doctrine                                            │
│  - Accès aux données                                            │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                         ENTITIES                                 │
│  - Mapping ORM                                                  │
│  - Validations                                                  │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                        PostgreSQL                                │
└─────────────────────────────────────────────────────────────────┘
```

### 3.3 Frontend avec Symfony UX

```
┌─────────────────────────────────────────────────────────────────┐
│                      NAVIGATEUR                                  │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────────────────────────────────────────────────────┐│
│  │                    Turbo Drive                               ││
│  │           (Navigation sans rechargement)                     ││
│  └─────────────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────────────┐│
│  │                   Turbo Frames                               ││
│  │         (Mise à jour partielle de la page)                  ││
│  └─────────────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────────────┐│
│  │                  Stimulus Controllers                        ││
│  │            (Interactivité JavaScript)                        ││
│  │  - dropdown_controller.js                                   ││
│  │  - modal_controller.js                                      ││
│  │  - search_controller.js                                     ││
│  │  - chart_controller.js                                      ││
│  └─────────────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────────────┐│
│  │                  Live Components                             ││
│  │         (Composants réactifs côté serveur)                  ││
│  │  - Recherche en temps réel                                  ││
│  │  - Filtres dynamiques                                       ││
│  │  - Formulaires interactifs                                  ││
│  └─────────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────────┘
```

---

## 4. Modèle de données

### 4.1 Diagramme entité-relation

```
┌─────────────────┐       ┌─────────────────┐       ┌─────────────────┐
│     Emetteur    │       │      User       │       │     Module      │
├─────────────────┤       ├─────────────────┤       ├─────────────────┤
│ id              │       │ id              │       │ id              │
│ raisonSociale   │       │ nom             │       │ nom             │
│ formeJuridique  │       │ email           │       │ prixDefaut      │
│ capital         │       │ password        │       │ tauxTva         │
│ adresse         │       │ roles[]         │       │ actif           │
│ siren           │       │ actif           │       │ createdAt       │
│ tva             │       │ createdAt       │       │ updatedAt       │
│ email           │       │ updatedAt       │       └─────────────────┘
│ telephone       │       └─────────────────┘
│ iban            │
│ bic             │
│ logo            │
└─────────────────┘

┌─────────────────┐       ┌─────────────────┐       ┌─────────────────┐
│     Client      │       │    Contact      │       │   ClientNote    │
├─────────────────┤       ├─────────────────┤       ├─────────────────┤
│ id              │───┐   │ id              │       │ id              │
│ nom             │   │   │ client       ───┼───────│ client       ───│
│ siren           │   │   │ nom             │       │ contenu         │
│ tva             │   └──▶│ prenom          │       │ createdAt       │
│ adresse         │       │ telephone       │       │ createdBy       │
│ iban            │       │ email           │       └─────────────────┘
│ statut          │       │ note            │
│ createdAt       │       │ createdAt       │
│ updatedAt       │       └─────────────────┘
└─────────────────┘
        │
        │ 1:N
        ▼
┌─────────────────┐       ┌─────────────────┐       ┌─────────────────┐
│   ClientLien    │       │    Contrat      │       │  LigneContrat   │
├─────────────────┤       ├─────────────────┤       ├─────────────────┤
│ id              │       │ id              │───┐   │ id              │
│ client       ───┼───────│ client       ───│   │   │ contrat      ───│
│ clientLie    ───┼───────│ instance     ───│   └──▶│ module       ───│
│ commentaire     │       │ dateSignature   │       │ quantite        │
│ createdAt       │       │ dateAnniversaire│       │ prixUnitaire    │
└─────────────────┘       │ periodicite     │       │ remise          │
                          │ factureParticuliere     │ tauxTva         │
                          │ statut          │       │ createdAt       │
                          │ createdAt       │       │ updatedAt       │
                          │ updatedAt       │       └─────────────────┘
                          └─────────────────┘
                                  │
        ┌─────────────────────────┼─────────────────────────┐
        │                         │                         │
        ▼                         ▼                         ▼
┌─────────────────┐       ┌─────────────────┐       ┌─────────────────┐
│ContratEvenement │       │  ContratFichier │       │   ContratCgv    │
├─────────────────┤       ├─────────────────┤       ├─────────────────┤
│ id              │       │ id              │       │ id              │
│ contrat      ───│       │ contrat      ───│       │ contrat      ───│
│ type            │       │ nom             │       │ cgv          ───│
│ description     │       │ chemin          │       │ dateDebut       │
│ createdAt       │       │ createdAt       │       │ dateFin         │
└─────────────────┘       └─────────────────┘       └─────────────────┘

┌─────────────────┐       ┌─────────────────┐       ┌─────────────────┐
│    Instance     │       │ InstanceClient  │       │  InstanceNom    │
├─────────────────┤       ├─────────────────┤       ├─────────────────┤
│ id              │───┐   │ id              │       │ id              │
│ nomActuel       │   │   │ instance     ───│       │ instance     ───│
│ createdAt       │   └──▶│ client       ───│       │ ancienNom       │
│ updatedAt       │       │ pourcentage     │       │ dateChangement  │
└─────────────────┘       │ createdAt       │       │ createdAt       │
                          └─────────────────┘       └─────────────────┘

┌─────────────────┐       ┌─────────────────┐       ┌─────────────────┐
│ ImportLicence   │       │  ReleveLicence  │       │    Facture      │
├─────────────────┤       ├─────────────────┤       ├─────────────────┤
│ id              │───┐   │ id              │       │ id              │
│ fichierOrigine  │   │   │ import       ───│       │ numero          │
│ dateImport      │   └──▶│ instance     ───│       │ contrat      ───│
│ nbLignes        │       │ dateReleve      │       │ client       ───│
│ statut          │       │ nbLicences      │       │ dateEmission    │
│ createdBy       │       │ statut          │       │ dateEcheance    │
│ createdAt       │       │ appliqueLe      │       │ montantHt       │
└─────────────────┘       │ createdAt       │       │ montantTva      │
                          └─────────────────┘       │ montantTtc      │
                                                    │ statut          │
┌─────────────────┐       ┌─────────────────┐       │ pdfChemin       │
│  LigneFacture   │       │  FactureEnvoi   │       │ createdAt       │
├─────────────────┤       ├─────────────────┤       │ updatedAt       │
│ id              │       │ id              │       └─────────────────┘
│ facture      ───┼───────│ facture      ───│               │
│ moduleNom       │       │ email           │               │
│ quantite        │       │ dateEnvoi       │               │
│ prixUnitaire    │       │ statut          │               │
│ remise          │       │ createdAt       │               │
│ tauxTva         │       └─────────────────┘               │
│ montantHt       │                                         │
└─────────────────┘◀────────────────────────────────────────┘

┌─────────────────┐       ┌─────────────────┐
│       Cgv       │       │ HistoriqueLicence│
├─────────────────┤       ├─────────────────┤
│ id              │       │ id              │
│ nom             │       │ contrat      ───│
│ fichierChemin   │       │ nbLicences      │
│ dateDebut       │       │ dateEffet       │
│ dateFin         │       │ releve       ───│
│ parDefaut       │       │ createdAt       │
│ createdAt       │       └─────────────────┘
└─────────────────┘
```

### 4.2 Entités Doctrine (exemples)

#### Client.php
```php
<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $nom = null;

    #[ORM\Column(length: 9)]
    #[Assert\NotBlank]
    #[Assert\Length(exactly: 9)]
    private ?string $siren = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $tva = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $adresse = null;

    #[ORM\Column(length: 34, nullable: true)]
    private ?string $iban = null;

    #[ORM\Column(length: 20)]
    private ?string $statut = 'actif';

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Contact::class, cascade: ['persist', 'remove'])]
    private Collection $contacts;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Contrat::class)]
    private Collection $contrats;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: ClientNote::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $notes;

    public function __construct()
    {
        $this->contacts = new ArrayCollection();
        $this->contrats = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Getters et Setters...

    public function getNbLicences(): int
    {
        $total = 0;
        foreach ($this->contrats as $contrat) {
            if ($contrat->getStatut() === 'actif') {
                foreach ($contrat->getLignes() as $ligne) {
                    $total += $ligne->getQuantite();
                }
            }
        }
        return $total;
    }
}
```

#### Facture.php
```php
<?php

namespace App\Entity;

use App\Repository\FactureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactureRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Facture
{
    public const STATUT_BROUILLON = 'brouillon';
    public const STATUT_VALIDEE = 'validee';
    public const STATUT_ENVOYEE = 'envoyee';
    public const STATUT_PAYEE = 'payee';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, nullable: true, unique: true)]
    private ?string $numero = null;

    #[ORM\ManyToOne(inversedBy: 'factures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Contrat $contrat = null;

    #[ORM\ManyToOne(inversedBy: 'factures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateEmission = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateEcheance = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    private ?string $montantHt = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    private ?string $montantTva = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    private ?string $montantTtc = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $remiseGlobale = null;

    #[ORM\Column(length: 20)]
    private ?string $statut = self::STATUT_BROUILLON;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pdfChemin = null;

    #[ORM\OneToMany(mappedBy: 'facture', targetEntity: LigneFacture::class, cascade: ['persist', 'remove'])]
    private Collection $lignes;

    #[ORM\OneToMany(mappedBy: 'facture', targetEntity: FactureEnvoi::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['dateEnvoi' => 'DESC'])]
    private Collection $envois;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->lignes = new ArrayCollection();
        $this->envois = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isModifiable(): bool
    {
        return $this->statut === self::STATUT_BROUILLON;
    }

    public function canRetourBrouillon(): bool
    {
        return $this->statut === self::STATUT_VALIDEE && $this->envois->isEmpty();
    }

    public function getDernierEnvoi(): ?FactureEnvoi
    {
        return $this->envois->first() ?: null;
    }

    // Getters et Setters...
}
```

---

## 5. Routes et Controllers

### 5.1 Structure des routes

```yaml
# config/routes.yaml

# Dashboard
dashboard:
    path: /
    controller: App\Controller\DashboardController::index

# Clients
client_index:
    path: /clients
    controller: App\Controller\ClientController::index
client_new:
    path: /clients/nouveau
    controller: App\Controller\ClientController::new
client_show:
    path: /clients/{id}
    controller: App\Controller\ClientController::show
client_edit:
    path: /clients/{id}/modifier
    controller: App\Controller\ClientController::edit
client_export:
    path: /clients/export
    controller: App\Controller\ClientController::export

# Contrats
contrat_index:
    path: /contrats
    controller: App\Controller\ContratController::index
contrat_new:
    path: /contrats/nouveau
    controller: App\Controller\ContratController::new
contrat_show:
    path: /contrats/{id}
    controller: App\Controller\ContratController::show
contrat_edit:
    path: /contrats/{id}/modifier
    controller: App\Controller\ContratController::edit
contrat_export:
    path: /contrats/export
    controller: App\Controller\ContratController::export

# Licences
licence_import:
    path: /licences/import
    controller: App\Controller\LicenceController::import
licence_releves:
    path: /licences/releves
    controller: App\Controller\LicenceController::releves
licence_mapping:
    path: /licences/mapping
    controller: App\Controller\LicenceController::mapping

# Facturation
facturation_workflow:
    path: /facturation
    controller: App\Controller\FacturationController::workflow
facturation_calculer:
    path: /facturation/calculer
    controller: App\Controller\FacturationController::calculer
    methods: POST
facturation_creer:
    path: /facturation/creer/{contratId}
    controller: App\Controller\FacturationController::creer
    methods: POST
facturation_valider:
    path: /facturation/{id}/valider
    controller: App\Controller\FacturationController::valider
    methods: POST
facturation_supprimer:
    path: /facturation/{id}/supprimer
    controller: App\Controller\FacturationController::supprimer
    methods: POST
facturation_pdf:
    path: /facturation/{id}/pdf
    controller: App\Controller\FacturationController::pdf
facturation_envoyer:
    path: /facturation/{id}/envoyer
    controller: App\Controller\FacturationController::envoyer
    methods: POST
facture_liste:
    path: /factures
    controller: App\Controller\FacturationController::liste
facture_export:
    path: /factures/export
    controller: App\Controller\FacturationController::export

# Admin
admin_emetteur:
    path: /admin/emetteur
    controller: App\Controller\Admin\EmetteurController::index
admin_modules:
    path: /admin/modules
    controller: App\Controller\Admin\ModuleController::index
admin_cgv:
    path: /admin/cgv
    controller: App\Controller\Admin\CgvController::index
admin_facturation:
    path: /admin/facturation
    controller: App\Controller\Admin\ParametreFacturationController::index
admin_users:
    path: /admin/utilisateurs
    controller: App\Controller\Admin\UserController::index
```

### 5.2 Exemple de Controller

```php
<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use App\Service\Client\ClientService;
use App\Service\Export\CsvExportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/clients')]
class ClientController extends AbstractController
{
    public function __construct(
        private ClientRepository $clientRepository,
        private ClientService $clientService,
        private EntityManagerInterface $em,
        private CsvExportService $csvExport,
    ) {}

    #[Route('', name: 'client_index')]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search', '');
        $showInactifs = $request->query->getBoolean('inactifs', false);
        $page = $request->query->getInt('page', 1);

        $clients = $this->clientRepository->findByFilters(
            search: $search,
            showInactifs: $showInactifs,
            page: $page,
            limit: 20
        );

        return $this->render('client/index.html.twig', [
            'clients' => $clients,
            'search' => $search,
            'showInactifs' => $showInactifs,
        ]);
    }

    #[Route('/nouveau', name: 'client_new')]
    public function new(Request $request): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Validation SIREN et IBAN
            $errors = $this->clientService->validate($client);
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->render('client/_form.html.twig', [
                    'form' => $form,
                    'client' => $client,
                ]);
            }

            $this->em->persist($client);
            $this->em->flush();

            $this->addFlash('success', 'Client créé avec succès.');
            return $this->redirectToRoute('client_show', ['id' => $client->getId()]);
        }

        return $this->render('client/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'client_show')]
    public function show(Client $client): Response
    {
        return $this->render('client/show.html.twig', [
            'client' => $client,
        ]);
    }

    #[Route('/{id}/modifier', name: 'client_edit')]
    public function edit(Request $request, Client $client): Response
    {
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $this->clientService->validate($client);
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->render('client/edit.html.twig', [
                    'form' => $form,
                    'client' => $client,
                ]);
            }

            $this->em->flush();
            $this->addFlash('success', 'Client modifié avec succès.');
            return $this->redirectToRoute('client_show', ['id' => $client->getId()]);
        }

        return $this->render('client/edit.html.twig', [
            'form' => $form,
            'client' => $client,
        ]);
    }

    #[Route('/export', name: 'client_export')]
    public function export(Request $request): Response
    {
        $search = $request->query->get('search', '');
        $showInactifs = $request->query->getBoolean('inactifs', false);

        $clients = $this->clientRepository->findByFilters(
            search: $search,
            showInactifs: $showInactifs,
            page: 1,
            limit: 10000
        );

        return $this->csvExport->exportClients($clients);
    }
}
```

---

## 6. Services métier

### 6.1 Service de facturation

```php
<?php

namespace App\Service\Facturation;

use App\Entity\Contrat;
use App\Entity\Facture;
use App\Entity\LigneFacture;
use App\Repository\ContratRepository;
use App\Repository\ParametreFacturationRepository;
use Doctrine\ORM\EntityManagerInterface;

class CalculFactureService
{
    public function __construct(
        private ContratRepository $contratRepository,
        private ParametreFacturationRepository $parametreRepo,
        private EntityManagerInterface $em,
    ) {}

    /**
     * Calcule les factures à créer pour le mois en cours
     */
    public function calculerFacturesACreer(): array
    {
        $contrats = $this->contratRepository->findContratsAFacturer();
        $factures = [];

        foreach ($contrats as $contrat) {
            $factures[] = [
                'contrat' => $contrat,
                'client' => $contrat->getClient(),
                'montantHt' => $this->calculerMontantHt($contrat),
                'alertes' => $this->getAlertes($contrat),
            ];
        }

        return $factures;
    }

    /**
     * Crée un brouillon de facture pour un contrat
     */
    public function creerBrouillon(Contrat $contrat): Facture
    {
        $facture = new Facture();
        $facture->setContrat($contrat);
        $facture->setClient($contrat->getClient());
        $facture->setStatut(Facture::STATUT_BROUILLON);

        // Copier les lignes du contrat
        foreach ($contrat->getLignes() as $ligneContrat) {
            $ligneFacture = new LigneFacture();
            $ligneFacture->setModuleNom($ligneContrat->getModule()->getNom());
            $ligneFacture->setQuantite($ligneContrat->getQuantite());
            $ligneFacture->setPrixUnitaire($ligneContrat->getPrixUnitaire());
            $ligneFacture->setRemise($ligneContrat->getRemise());
            $ligneFacture->setTauxTva($ligneContrat->getTauxTva());
            $ligneFacture->calculerMontantHt();
            $facture->addLigne($ligneFacture);
        }

        $facture->calculerTotaux();

        $this->em->persist($facture);
        $this->em->flush();

        return $facture;
    }

    /**
     * Valide une facture (attribue le numéro définitif)
     */
    public function valider(Facture $facture): void
    {
        if (!$facture->isModifiable()) {
            throw new \LogicException('Cette facture ne peut pas être validée.');
        }

        $parametres = $this->parametreRepo->getParametres();
        $numero = $this->genererNumero($parametres);

        $facture->setNumero($numero);
        $facture->setDateEmission(new \DateTime());
        $facture->setDateEcheance(
            (new \DateTime())->modify('+' . $parametres->getDelaiEcheance() . ' days')
        );
        $facture->setStatut(Facture::STATUT_VALIDEE);

        $this->em->flush();
    }

    private function calculerMontantHt(Contrat $contrat): float
    {
        $total = 0;
        foreach ($contrat->getLignes() as $ligne) {
            $montant = $ligne->getQuantite() * $ligne->getPrixUnitaire();
            if ($ligne->getRemise()) {
                $montant *= (1 - $ligne->getRemise() / 100);
            }
            $total += $montant;
        }
        return $total;
    }

    private function getAlertes(Contrat $contrat): array
    {
        $alertes = [];

        if ($contrat->isFactureParticuliere()) {
            $alertes[] = ['type' => 'warning', 'message' => 'Facture particulière'];
        }

        // Vérifier écart licences
        $ecart = $this->calculerEcartLicences($contrat);
        if (abs($ecart) > 10) {
            $alertes[] = ['type' => 'danger', 'message' => "Écart licences: {$ecart}%"];
        }

        return $alertes;
    }

    private function genererNumero($parametres): string
    {
        $format = $parametres->getFormatNumero();
        $numero = $parametres->getProchainNumero();

        $result = str_replace('{YYYY}', date('Y'), $format);
        $result = preg_replace_callback('/\{SEQ:(\d+)\}/', function($matches) use ($numero) {
            return str_pad($numero, (int)$matches[1], '0', STR_PAD_LEFT);
        }, $result);

        $parametres->setProchainNumero($numero + 1);

        return $result;
    }
}
```

### 6.2 Service de génération PDF

```php
<?php

namespace App\Service\Pdf;

use App\Entity\Facture;
use App\Repository\EmetteurRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class PdfGeneratorService
{
    public function __construct(
        private Environment $twig,
        private EmetteurRepository $emetteurRepository,
        private string $storagePath,
    ) {}

    public function generateFacture(Facture $facture): string
    {
        $emetteur = $this->emetteurRepository->getEmetteur();

        $html = $this->twig->render('pdf/facture.html.twig', [
            'facture' => $facture,
            'emetteur' => $emetteur,
        ]);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfContent = $dompdf->output();

        // Sauvegarder le PDF
        $filename = sprintf('facture_%s.pdf', $facture->getNumero() ?? $facture->getId());
        $path = $this->storagePath . '/factures/' . $filename;

        file_put_contents($path, $pdfContent);

        return $path;
    }
}
```

### 6.3 Service de validation SIREN/IBAN

```php
<?php

namespace App\Service\Validation;

class SirenValidator
{
    public function validate(string $siren): array
    {
        $siren = preg_replace('/\s/', '', $siren);

        if (!preg_match('/^\d{9}$/', $siren)) {
            return ['valid' => false, 'error' => 'Le SIREN doit contenir 9 chiffres'];
        }

        // Algorithme de Luhn
        if (!$this->luhnCheck($siren)) {
            return ['valid' => false, 'error' => 'Le SIREN est invalide (clé de contrôle)'];
        }

        return ['valid' => true];
    }

    private function luhnCheck(string $number): bool
    {
        $sum = 0;
        $length = strlen($number);

        for ($i = 0; $i < $length; $i++) {
            $digit = (int)$number[$length - 1 - $i];

            if ($i % 2 === 1) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return $sum % 10 === 0;
    }
}

class IbanValidator
{
    public function validate(string $iban): array
    {
        $iban = strtoupper(preg_replace('/\s/', '', $iban));

        if (!preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]{1,30}$/', $iban)) {
            return ['valid' => false, 'error' => 'Format IBAN invalide'];
        }

        // Vérification clé de contrôle
        $rearranged = substr($iban, 4) . substr($iban, 0, 4);
        $numeric = '';

        for ($i = 0; $i < strlen($rearranged); $i++) {
            $char = $rearranged[$i];
            if (ctype_alpha($char)) {
                $numeric .= ord($char) - 55;
            } else {
                $numeric .= $char;
            }
        }

        if (bcmod($numeric, '97') !== '1') {
            return ['valid' => false, 'error' => 'IBAN invalide (clé de contrôle)'];
        }

        return ['valid' => true];
    }
}
```

---

## 7. Sécurité

### 7.1 Authentification

```yaml
# config/packages/security.yaml
security:
    password_hashers:
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'

    providers:
        app_user_provider:
            entity:
                class: App\Entity\User
                property: email

    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main:
            lazy: true
            provider: app_user_provider
            form_login:
                login_path: app_login
                check_path: app_login
                default_target_path: dashboard
            logout:
                path: app_logout
                target: app_login
            remember_me:
                secret: '%kernel.secret%'
                lifetime: 604800 # 1 semaine

    access_control:
        - { path: ^/login, roles: PUBLIC_ACCESS }
        - { path: ^/admin, roles: ROLE_ADMIN }
        - { path: ^/, roles: ROLE_USER }
```

### 7.2 Protection CSRF

```php
// Dans les formulaires
$form = $this->createForm(ClientType::class, $client, [
    'csrf_protection' => true,
    'csrf_field_name' => '_token',
    'csrf_token_id'   => 'client_form',
]);
```

### 7.3 Chiffrement des données sensibles

```php
<?php

namespace App\Service;

class EncryptionService
{
    private string $key;

    public function __construct(string $encryptionKey)
    {
        $this->key = $encryptionKey;
    }

    public function encrypt(string $data): string
    {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $this->key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    public function decrypt(string $data): string
    {
        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $this->key, 0, $iv);
    }
}
```

---

## 8. Déploiement VPS

### 8.1 Prérequis serveur

| Composant | Version |
|-----------|---------|
| OS | Ubuntu 22.04 LTS / Debian 12 |
| PHP | 8.3+ |
| PostgreSQL | 16+ |
| Nginx | 1.24+ |
| Composer | 2.x |
| Node.js | 20 LTS (pour build assets) |

### 8.2 Configuration Nginx

```nginx
# /etc/nginx/sites-available/factu
server {
    listen 80;
    server_name factu.example.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name factu.example.com;

    ssl_certificate /etc/letsencrypt/live/factu.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/factu.example.com/privkey.pem;

    root /var/www/factu/public;
    index index.php;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        internal;
    }

    location ~ \.php$ {
        return 404;
    }

    # Fichiers statiques
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Sécurité
    location ~ /\. {
        deny all;
    }

    error_log /var/log/nginx/factu_error.log;
    access_log /var/log/nginx/factu_access.log;
}
```

### 8.3 Configuration PHP-FPM

```ini
; /etc/php/8.3/fpm/pool.d/factu.conf
[factu]
user = www-data
group = www-data
listen = /var/run/php/php8.3-fpm-factu.sock
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 10
pm.start_servers = 3
pm.min_spare_servers = 2
pm.max_spare_servers = 5

php_admin_value[memory_limit] = 256M
php_admin_value[upload_max_filesize] = 20M
php_admin_value[post_max_size] = 20M
```

### 8.4 Variables d'environnement

```bash
# /var/www/factu/.env.local
APP_ENV=prod
APP_SECRET=your-secret-key-here

DATABASE_URL="postgresql://factu_user:password@localhost:5432/factu_db?serverVersion=16"

MAILER_DSN=smtp://user:pass@smtp.example.com:587

STORAGE_PATH=/var/www/factu/storage
ENCRYPTION_KEY=your-encryption-key-here
```

### 8.5 Script de déploiement

```bash
#!/bin/bash
# deploy.sh

set -e

cd /var/www/factu

# Maintenance mode
php bin/console app:maintenance:on

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Build assets
npm ci
npm run build

# Database migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Clear cache
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# Fix permissions
chown -R www-data:www-data var/ storage/

# Restart PHP-FPM
sudo systemctl restart php8.3-fpm

# Maintenance mode off
php bin/console app:maintenance:off

echo "Deployment completed!"
```

### 8.6 Sauvegarde

```bash
#!/bin/bash
# backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR=/var/backups/factu

# Backup base de données
pg_dump -U factu_user factu_db | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup fichiers
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz /var/www/factu/storage

# Retention 30 jours
find $BACKUP_DIR -type f -mtime +30 -delete
```

### 8.7 Cron jobs

```cron
# /etc/cron.d/factu

# Sauvegarde quotidienne à 2h
0 2 * * * root /var/www/factu/scripts/backup.sh

# Nettoyage cache (optionnel)
0 3 * * 0 www-data php /var/www/factu/bin/console cache:clear --env=prod
```

---

## 9. Monitoring

### 9.1 Logs Symfony

```yaml
# config/packages/monolog.yaml
when@prod:
    monolog:
        handlers:
            main:
                type: fingers_crossed
                action_level: error
                handler: nested
                buffer_size: 50
            nested:
                type: rotating_file
                path: "%kernel.logs_dir%/%kernel.environment%.log"
                level: info
                max_files: 30
```

### 9.2 Healthcheck endpoint

```php
<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class HealthController
{
    #[Route('/health', name: 'health_check')]
    public function check(Connection $connection): JsonResponse
    {
        try {
            $connection->executeQuery('SELECT 1');
            $dbStatus = 'ok';
        } catch (\Exception $e) {
            $dbStatus = 'error';
        }

        return new JsonResponse([
            'status' => $dbStatus === 'ok' ? 'healthy' : 'unhealthy',
            'database' => $dbStatus,
            'timestamp' => date('c'),
        ]);
    }
}
```

---

## 10. Annexes

### 10.1 Commandes utiles

```bash
# Création projet
composer create-project symfony/skeleton factu
cd factu

# Installation bundles
composer require symfony/orm-pack
composer require symfony/twig-pack
composer require symfony/security-bundle
composer require symfony/webpack-encore-bundle
composer require symfony/ux-turbo
composer require symfony/ux-live-component
composer require symfony/mailer
composer require dompdf/dompdf
composer require league/csv

# Création entités
php bin/console make:entity Client
php bin/console make:entity Contrat
# ...

# Migrations
php bin/console make:migration
php bin/console doctrine:migrations:migrate

# Fixtures (dev)
php bin/console doctrine:fixtures:load
```

### 10.2 Documents liés
- [Product Brief](./product-brief.md)
- [PRD](./prd.md)
- [Brainstorming](../analysis/brainstorming-session-2026-01-16.md)
