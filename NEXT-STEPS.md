# Prochaines etapes - Factu

**Date:** 2026-01-21
**Derniere session:** Sprint 2 Corrections termine
**Prochaine session:** Sprint 3 - Clients

---

## Etat actuel du projet

Les Sprints 1 (Setup + Auth) et 2 (Parametres) sont termines, y compris les corrections majeures du Sprint 2 :
- Authentification complete
- **Multi-emetteurs avec versioning** (nouvelle architecture)
- Gestion des modules (catalogue produits)
- **Bibliotheque CGV commune** avec association par emetteur
- **Parametres de facturation par emetteur**

### Architecture Multi-emetteurs

Le Sprint 2 a ete refactorise pour supporter :
- **Plusieurs emetteurs** (entites juridiques ou configurations)
- **Versioning des emetteurs** avec date d'effet (pour historiser RIB, adresse, etc.)
- **Parametres de facturation** lies a chaque emetteur
- **CGV en bibliotheque commune** avec association N:N aux emetteurs

### Structure du projet

```
D:/ClaudeProjects/Factu/
├── _bmad-output/           # Documentation projet
│   ├── analysis/
│   ├── planning-artifacts/
│   │   ├── spec-sprint2-corrections.md  # Specification des corrections
│   │   └── ...
│   ├── README.md
│   ├── NEXT-STEPS.md
│   └── CHANGELOG.md
│
└── app/                    # Application Symfony
    ├── src/
    │   ├── Controller/
    │   │   ├── Admin/
    │   │   │   ├── AdminController.php
    │   │   │   ├── EmetteurController.php  # Multi-emetteurs avec onglets
    │   │   │   ├── ModuleController.php
    │   │   │   └── CgvController.php       # Bibliotheque commune
    │   │   └── ...
    │   ├── Entity/
    │   │   ├── User.php
    │   │   ├── Emetteur.php           # Code, nom, actif, parDefaut
    │   │   ├── EmetteurVersion.php    # NOUVEAU - Versioning des infos
    │   │   ├── EmetteurCgv.php        # NOUVEAU - Association emetteur/CGV
    │   │   ├── Module.php
    │   │   ├── Cgv.php                # Bibliotheque commune (sans parDefaut)
    │   │   └── ParametreFacturation.php  # Lie a un emetteur
    │   ├── Form/
    │   │   ├── EmetteurType.php
    │   │   ├── EmetteurVersionType.php   # NOUVEAU
    │   │   ├── ModuleType.php
    │   │   ├── CgvType.php
    │   │   └── ParametreFacturationType.php
    │   └── Repository/
    │       ├── EmetteurRepository.php
    │       ├── EmetteurVersionRepository.php   # NOUVEAU
    │       ├── EmetteurCgvRepository.php       # NOUVEAU
    │       └── ...
    ├── templates/
    │   ├── admin/
    │   │   ├── emetteurs/      # NOUVEAU - Interface avec onglets
    │   │   │   ├── index.html.twig
    │   │   │   ├── new.html.twig
    │   │   │   ├── edit.html.twig
    │   │   │   ├── show.html.twig    # Vue avec onglets
    │   │   │   ├── version_new.html.twig
    │   │   │   └── params.html.twig
    │   │   ├── modules/
    │   │   ├── cgv/            # Bibliotheque avec codes emetteurs
    │   │   └── users.html.twig
    │   └── ...
    └── tests/
        └── test_sprint2_corrections.php  # Tests valides
```

---

## A faire pour demarrer

### 1. Lancer Laragon
Demarrer MySQL via l'interface Laragon.

### 2. Compiler les assets
```bash
cd D:/ClaudeProjects/Factu/app
npm run watch
```

### 3. Lancer le serveur
```bash
D:/laragon/bin/php/php-8.3.28-Win32-vs16-x64/php.exe -S localhost:8000 -t public
```

### 4. Creer un utilisateur admin (si pas fait)
```bash
D:/laragon/bin/php/php-8.3.28-Win32-vs16-x64/php.exe bin/console security:hash-password
# Utiliser le hash genere pour creer un user en base
```

---

## Checklist Sprint 1 - TERMINE

- [x] E0-S1 : Initialiser le projet Symfony 7
- [x] E0-S2 : Configurer base de donnees
- [x] E0-S3 : Configurer Webpack Encore + Tailwind
- [x] E0-S4 : Configurer Symfony UX
- [x] E0-S5 : Creer le layout de base
- [x] E0-S6 : Creer les composants UI
- [x] E0-S7 : Configurer les variables d'environnement
- [x] E0-S8 : Structure de dossiers
- [x] E1-S1 : Creer l'entite User
- [x] E1-S2 : Page de login
- [x] E1-S3 : Configurer securite Symfony
- [x] E1-S4 : Deconnexion
- [x] E1-S5 : Proteger les routes

---

## Checklist Sprint 2 - TERMINE (avec corrections)

- [x] E2-S1 : Creer l'entite Emetteur (refactorise pour multi-emetteurs)
- [x] E2-S2 : Formulaire emetteur (nouveau: code, nom, actif, parDefaut)
- [x] E2-S3 : Creer l'entite Module
- [x] E2-S4 : CRUD Modules
- [x] E2-S5 : Creer l'entite CGV (refactorise: bibliotheque commune)
- [x] E2-S6 : CRUD CGV avec upload
- [x] E2-S7 : Creer l'entite ParametreFacturation (lie a emetteur)
- [x] E2-S8 : Ecran parametres facturation (integre dans fiche emetteur)

### Corrections Sprint 2 (nouvelles entites)
- [x] EmetteurVersion : Versioning avec date d'effet
- [x] EmetteurCgv : Association N:N emetteur/CGV
- [x] Interface emetteur avec 4 onglets (Infos, Versions, Params, CGV)
- [x] Format numero avec variables {CODE}, {SIREN}, etc.

---

## Sprint 3 - Clients (E3)

| ID | Story | Points |
|----|-------|--------|
| E3-S1 | Creer les entites Client, Contact, ClientNote, ClientLien | 3 |
| E3-S2 | Implementer la liste des clients | 3 |
| E3-S3 | Implementer la recherche clients | 2 |
| E3-S4 | Implementer le filtre clients inactifs | 1 |
| E3-S5 | Implementer l'export CSV clients | 2 |
| E3-S6 | Implementer la fiche client 360 | 5 |
| E3-S7 | Formulaire creation/modification client | 3 |
| E3-S8 | Validation SIREN | 2 |
| E3-S9 | Validation IBAN | 2 |
| E3-S10 | Gestion des contacts | 3 |
| E3-S11 | Notes horodatees | 2 |
| E3-S12 | Liens entre clients | 2 |

**Note:** Le contrat devra avoir un champ `emetteur_id` pour supporter multi-emetteurs.

---

## Routes disponibles

| Route | URL | Description |
|-------|-----|-------------|
| app_dashboard | / | Tableau de bord |
| app_client_index | /clients | Liste clients |
| app_client_show | /clients/{id} | Fiche client |
| app_contrat_index | /contrats | Liste contrats |
| app_contrat_show | /contrats/{id} | Fiche contrat |
| app_licence_index | /licences | Gestion licences |
| app_licence_import | /licences/import | Import CSV |
| app_licence_releves | /licences/releves | Historique releves |
| app_facturation_workflow | /facturation | Workflow 3 colonnes |
| app_facturation_liste | /facturation/liste | Liste factures |
| **app_admin_emetteurs** | /admin/emetteurs | **Liste des emetteurs** |
| app_admin_emetteurs_show | /admin/emetteurs/{id} | Fiche emetteur (onglets) |
| app_admin_emetteurs_version_new | /admin/emetteurs/{id}/version | Nouvelle version |
| app_admin_emetteurs_params | /admin/emetteurs/{id}/parametres | Parametres facturation |
| app_admin_modules | /admin/modules | Gestion modules |
| app_admin_cgv | /admin/cgv | **Bibliotheque CGV** |
| app_admin_users | /admin/users | Gestion utilisateurs |
| app_login | /login | Page connexion |
| app_logout | /logout | Deconnexion |

---

## Variables de numerotation disponibles

Le format de numero de facture supporte :

| Variable | Description | Exemple |
|----------|-------------|---------|
| {YYYY} | Annee sur 4 chiffres | 2026 |
| {YY} | Annee sur 2 chiffres | 26 |
| {MM} | Mois sur 2 chiffres | 01 |
| {SEQ:N} | Sequence sur N chiffres | 00042 |
| {CODE} | Code de l'emetteur | ZK |
| {SIREN} | SIREN de l'emetteur | 123456789 |

Exemple: `{CODE}-{YYYY}-{SEQ:5}` donne `ZK-2026-00042`

---

## Fichiers de reference

| Besoin | Document |
|--------|----------|
| Specifications ecrans | `planning-artifacts/prd.md` |
| Modele de donnees | `planning-artifacts/architecture.md` (section 4) |
| Code exemple (entites, services) | `planning-artifacts/architecture.md` (sections 4.2, 5, 6) |
| Stories detaillees | `planning-artifacts/epics-stories.md` |
| **Spec corrections Sprint 2** | `planning-artifacts/spec-sprint2-corrections.md` |

---

## Notes techniques

- **PHP:** D:/laragon/bin/php/php-8.3.28-Win32-vs16-x64/php.exe
- **Composer:** D:/laragon/bin/composer/composer.phar
- **MySQL (dev):** root@localhost:3306/factu_db
- **PostgreSQL (prod):** A configurer sur le VPS
- **Tailwind CSS:** v4 avec @tailwindcss/postcss
