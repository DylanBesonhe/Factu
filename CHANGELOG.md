# Changelog - Factu

Toutes les modifications notables du projet sont documentees dans ce fichier.

---

## [0.2.0] - 2026-01-21

### Sprint 2 - Parametres

Deuxieme sprint du projet. Implementation du module de parametres.

### Ajoute

#### Entites (E2)
- **Emetteur** (`src/Entity/Emetteur.php`)
  - Champs: raisonSociale, formeJuridique, capital, adresse, siren, tva, email, telephone, iban, bic, logo
  - Validation SIREN (9 chiffres)
  - Formatage automatique IBAN/SIREN
  - Repository avec getOrCreateEmetteur()

- **Module** (`src/Entity/Module.php`)
  - Champs: nom, prixDefaut, tauxTva, actif, description
  - Contrainte d'unicite sur le nom
  - Repository avec findActifs(), findAllOrderedByName()

- **Cgv** (`src/Entity/Cgv.php`)
  - Champs: nom, fichierChemin, fichierOriginal, dateDebut, dateFin, parDefaut
  - Gestion du statut (Actif, A venir, Expire)
  - Repository avec findActives(), resetParDefaut()

- **ParametreFacturation** (`src/Entity/ParametreFacturation.php`)
  - Champs: formatNumero, prochainNumero, delaiEcheance, mentionsLegales, emailObjet, emailCorps
  - Methode genererNumero() pour generation automatique
  - Support des variables: {YYYY}, {MM}, {SEQ:N}

#### Controllers Admin
- **EmetteurController** - Formulaire emetteur unique
- **ModuleController** - CRUD complet (liste, creation, modification, activation/desactivation)
- **CgvController** - CRUD avec upload PDF, telechargement, suppression
- **ParametreFacturationController** - Formulaire avec apercu du prochain numero

#### Formulaires
- `EmetteurType` - Tous les champs emetteur avec validation
- `ModuleType` - Nom, prix, TVA, description, statut
- `CgvType` - Upload fichier PDF, dates, option par defaut
- `ParametreFacturationType` - Format numero, delai, email

#### Templates
- `admin/emetteur.html.twig` - Formulaire complet
- `admin/modules/index.html.twig` - Liste avec actions
- `admin/modules/new.html.twig` - Creation module
- `admin/modules/edit.html.twig` - Modification module
- `admin/cgv/index.html.twig` - Liste avec telechargement
- `admin/cgv/new.html.twig` - Upload nouvelle version
- `admin/cgv/edit.html.twig` - Modification version
- `admin/facturation.html.twig` - Parametres avec apercu
- `admin/users.html.twig` - Liste utilisateurs (lecture seule)

#### Navigation
- Sidebar mise a jour avec section Parametres
- Liens: Emetteur, Modules, CGV, Facturation, Utilisateurs

#### Storage
- Dossier `storage/cgv/` pour les fichiers PDF des CGV

### Routes ajoutees
| Route | Methode | URL |
|-------|---------|-----|
| app_admin_emetteur | ANY | /admin/emetteur |
| app_admin_modules | ANY | /admin/modules |
| app_admin_modules_new | ANY | /admin/modules/nouveau |
| app_admin_modules_edit | ANY | /admin/modules/{id}/modifier |
| app_admin_modules_toggle | POST | /admin/modules/{id}/toggle |
| app_admin_cgv | ANY | /admin/cgv |
| app_admin_cgv_new | ANY | /admin/cgv/nouveau |
| app_admin_cgv_edit | ANY | /admin/cgv/{id}/modifier |
| app_admin_cgv_delete | POST | /admin/cgv/{id}/supprimer |
| app_admin_cgv_download | ANY | /admin/cgv/{id}/telecharger |
| app_admin_facturation | ANY | /admin/facturation |

---

## [0.1.0] - 2026-01-21

### Sprint 1 - Setup & Authentification

Premier sprint du projet. Mise en place de l'infrastructure technique et de l'authentification.

### Ajoute

#### Infrastructure (E0)
- **Projet Symfony 7.4** initialise dans `D:/ClaudeProjects/Factu/app/`
- **Bundles installes:**
  - symfony/orm-pack (Doctrine ORM)
  - symfony/twig-pack (Templates)
  - symfony/security-bundle (Authentification)
  - symfony/form (Formulaires)
  - symfony/validator (Validation)
  - symfony/maker-bundle (Dev tools)
  - symfony/webpack-encore-bundle (Assets)
  - symfony/ux-turbo (Navigation SPA-like)
  - symfony/ux-live-component (Composants reactifs)
  - symfony/stimulus-bundle (JavaScript)
  - symfony/ux-chartjs (Graphiques)
  - symfony/mailer (Emails)
  - dompdf/dompdf (Generation PDF)
  - league/csv (Import/Export CSV)

- **Frontend:**
  - Tailwind CSS v4 avec @tailwindcss/postcss
  - Flowbite v4 (composants UI)
  - PostCSS configure
  - Webpack Encore configure

- **Base de donnees:**
  - MySQL 8.4 configure pour le developpement (Laragon)
  - PostgreSQL 16 prepare pour la production
  - Fichier `.env.local` avec configuration locale

#### Layout et Templates (E0)
- **Layout principal** (`templates/layout.html.twig`)
  - Sidebar navigation avec tous les modules
  - Header avec nom utilisateur et deconnexion
  - Zone de contenu avec flash messages
  - Design responsive (mobile-first)

- **Composants** (`templates/components/`)
  - `_sidebar.html.twig` - Navigation laterale
  - `_header.html.twig` - En-tete de page

- **Templates de pages:**
  - Dashboard avec indicateurs (placeholder)
  - Liste clients avec recherche et filtres
  - Fiche client (placeholder)
  - Liste contrats
  - Fiche contrat (placeholder)
  - Gestion licences (import, releves)
  - Workflow facturation (3 colonnes Kanban)
  - Liste factures
  - Admin: Emetteur, Modules, CGV, Utilisateurs

#### Authentification (E1)
- **Entite User** (`src/Entity/User.php`)
  - Champs: id, nom, email, password, roles, actif, createdAt, updatedAt
  - Implements UserInterface, PasswordAuthenticatedUserInterface
  - Lifecycle callbacks pour timestamps

- **Repository** (`src/Repository/UserRepository.php`)
  - Implements PasswordUpgraderInterface

- **Security** (`config/packages/security.yaml`)
  - Provider: entity (App\Entity\User)
  - Form login configure
  - Logout configure
  - Access control: toutes les routes protegees sauf /login

- **Page de login** (`templates/security/login.html.twig`)
  - Design moderne avec Tailwind
  - Gestion des erreurs
  - Protection CSRF

#### Controllers
- `DashboardController` - Route: /
- `ClientController` - Routes: /clients, /clients/{id}
- `ContratController` - Routes: /contrats, /contrats/{id}
- `LicenceController` - Routes: /licences, /licences/import, /licences/releves
- `FacturationController` - Routes: /facturation, /facturation/liste
- `Admin/AdminController` - Routes: /admin/*
- `SecurityController` - Routes: /login, /logout

### Configuration

#### Fichiers crees/modifies
```
app/
├── .env.local                              # Config locale (MySQL)
├── postcss.config.js                       # PostCSS pour Tailwind
├── webpack.config.js                       # Modifie (PostCSS active)
├── assets/
│   ├── app.js                              # Modifie (import Flowbite)
│   └── styles/app.css                      # Modifie (Tailwind v4)
├── config/
│   └── packages/security.yaml              # Modifie (auth complete)
├── src/
│   ├── Controller/
│   │   ├── DashboardController.php
│   │   ├── ClientController.php
│   │   ├── ContratController.php
│   │   ├── LicenceController.php
│   │   ├── FacturationController.php
│   │   ├── SecurityController.php
│   │   └── Admin/AdminController.php
│   ├── Entity/
│   │   └── User.php
│   └── Repository/
│       └── UserRepository.php
└── templates/
    ├── base.html.twig                      # Modifie
    ├── layout.html.twig                    # Nouveau
    ├── components/
    │   ├── _sidebar.html.twig
    │   └── _header.html.twig
    ├── dashboard/index.html.twig
    ├── client/
    │   ├── index.html.twig
    │   └── show.html.twig
    ├── contrat/
    │   ├── index.html.twig
    │   └── show.html.twig
    ├── licence/
    │   ├── index.html.twig
    │   ├── import.html.twig
    │   └── releves.html.twig
    ├── facturation/
    │   ├── workflow.html.twig
    │   └── liste.html.twig
    ├── admin/
    │   ├── emetteur.html.twig
    │   ├── modules.html.twig
    │   ├── cgv.html.twig
    │   └── users.html.twig
    └── security/
        └── login.html.twig
```

### Notes techniques

- **Environnement de dev:** Laragon (Windows)
- **PHP:** 8.3.28 (D:/laragon/bin/php/php-8.3.28-Win32-vs16-x64/)
- **Composer:** Via Laragon
- **Node:** 22.x
- **MySQL:** 8.4.3 (dev) - PostgreSQL 16 (prod)

### A faire (Sprint 2)

- Creer les entites: Emetteur, Module, CGV
- Implementer les formulaires de parametres
- CRUD complet pour Modules et CGV
- Gestion des utilisateurs

---

## [0.0.1] - 2026-01-20

### Planification

- Brainstorming termine
- Product Brief valide
- PRD redige (101 user stories, 15 ecrans)
- Architecture technique definie (Symfony 7)
- Epics & Stories decoupes (88 stories, 218 points, 9 epics)

---

## [0.0.0] - 2026-01-16

### Initialisation

- Projet initie
- Session de brainstorming demarree
