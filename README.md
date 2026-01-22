# Factu - Documentation Projet

**Derniere mise a jour:** 2026-01-22
**Statut:** En developpement - Sprint 3 termine

---

## Description

**Factu** est une application de facturation interne pour gerer :
- Vente d'abonnements logiciels multi-modules
- Suivi des licences avec tracabilite complete
- Generation et envoi des factures

### Contexte
- **Utilisateurs:** Comptabilite + Direction (4-5 personnes)
- **Clients:** ~400
- **Factures/mois:** ~300
- **Hebergement:** VPS

---

## Installation

### Prerequis
- Laragon (PHP 8.3, MySQL 8.4)
- Node.js et npm
- Composer

### Demarrage rapide

```bash
# 1. Aller dans le projet
cd D:/ClaudeProjects/Factu/app

# 2. Installer les dependances (si necessaire)
composer install
npm install

# 3. Demarrer Laragon (MySQL)

# 4. Creer la base de donnees
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# 5. Compiler les assets
npm run dev

# 6. Lancer le serveur
php -S localhost:8000 -t public
```

### Acces
- URL: http://localhost:8000
- Login: /login

---

## Documents de planification

| Document | Description | Fichier |
|----------|-------------|---------|
| **Brainstorming** | Exploration des besoins, UX, ecrans | `docs/analysis/brainstorming-session-2026-01-16.md` |
| **Product Brief** | Vision produit, objectifs, perimetre | `docs/planning-artifacts/product-brief.md` |
| **PRD** | Specifications fonctionnelles detaillees | `docs/planning-artifacts/prd.md` |
| **Architecture** | Choix techniques, modele de donnees, code | `docs/planning-artifacts/architecture.md` |
| **Epics & Stories** | Decoupage en taches de developpement | `docs/planning-artifacts/epics-stories.md` |
| **Changelog** | Historique des modifications | `CHANGELOG.md` |
| **Next Steps** | Prochaines etapes | `NEXT-STEPS.md` |

---

## Stack technique

| Couche | Technologie | Version |
|--------|-------------|---------|
| **Backend** | Symfony | 7.4 |
| **PHP** | PHP | 8.3 |
| **ORM** | Doctrine | 3.x |
| **Templates** | Twig | 3.x |
| **Frontend** | Symfony UX | 2.32 |
| **CSS** | Tailwind CSS | 4.x |
| **UI** | Flowbite | 4.x |
| **Database (dev)** | MySQL | 8.4 |
| **Database (prod)** | PostgreSQL | 16 |
| **PDF** | DomPDF | 3.x |
| **Email** | Symfony Mailer | 7.x |
| **CSV** | League CSV | 9.x |

---

## Structure du projet

```
D:/ClaudeProjects/Factu/
├── README.md                  # Ce fichier
├── CHANGELOG.md               # Historique des modifications
├── NEXT-STEPS.md              # Prochaines etapes
├── docs/                      # Documentation
│   ├── analysis/
│   ├── planning-artifacts/
│   ├── setup-bmad.md
│   └── setup-local.md
│
└── app/                       # Application Symfony 7
    ├── assets/                # Frontend
    │   ├── controllers/       # Stimulus controllers
    │   ├── styles/           # CSS (Tailwind)
    │   └── app.js
    ├── config/               # Configuration
    ├── migrations/           # Migrations Doctrine
    ├── public/               # Document root
    ├── src/
    │   ├── Controller/       # Controllers
    │   ├── Entity/          # Entites Doctrine
    │   └── Repository/      # Repositories
    ├── templates/           # Templates Twig
    │   ├── components/      # Composants reutilisables
    │   ├── dashboard/
    │   ├── client/
    │   ├── contrat/
    │   ├── licence/
    │   ├── facturation/
    │   ├── admin/
    │   └── security/
    └── var/                 # Cache, logs
```

---

## Modules V1

| Module | Description | Statut |
|--------|-------------|--------|
| **Dashboard** | Indicateurs cles (CA, clients, factures) | Template cree |
| **Clients** | CRUD, fiche 360, contacts, notes, liens | Fonctionnel |
| **Contrats** | CRUD, lignes tarifaires, evenements, fichiers | Template cree |
| **Licences** | Import CSV, mapping instances, traitement releves | Template cree |
| **Facturation** | Workflow 3 colonnes, generation PDF, envoi email | Template cree |
| **Parametres** | Emetteur, modules, CGV, utilisateurs | Fonctionnel |
| **Auth** | Login, logout, protection routes | Fonctionnel |

---

## Avancement

### Sprints

| Sprint | Epics | Stories | Points | Statut |
|--------|-------|---------|--------|--------|
| Sprint 1 | E0 Setup + E1 Auth | 13 | 26 | Termine |
| Sprint 2 | E2 Parametres | 8 | 20 | Termine |
| Sprint 3 | E3 Clients | 12 | 30 | Termine |
| Sprint 4 | E4 Contrats | 14 | 35 | A faire |
| Sprint 5 | E5 Licences | 12 | 32 | A faire |
| Sprint 6 | E6 Facturation | 16 | 45 | A faire |
| Sprint 7 | E7 Dashboard + E8 Finitions | 13 | 30 | A faire |

### Resume

| Metrique | Valeur |
|----------|--------|
| Epics | 9 |
| Stories | 88 |
| Points | 218 |
| Sprints | 7 |
| Termine | Sprint 1-3 (35%) |

---

## Commandes utiles

```bash
# Vider le cache
php bin/console cache:clear

# Compiler les assets (dev)
npm run dev

# Compiler les assets (watch)
npm run watch

# Compiler les assets (prod)
npm run build

# Voir les routes
php bin/console debug:router

# Creer une migration
php bin/console doctrine:migrations:diff

# Executer les migrations
php bin/console doctrine:migrations:migrate

# Hash un mot de passe
php bin/console security:hash-password
```

---

## Hors scope V1 (prevu V2)

- Suivi paiements / Rapprochement bancaire
- Avoirs
- Factures manuelles
- Roles et permissions utilisateurs
- Integration comptabilite
- API import licences

---

## Historique

| Date | Evenement |
|------|-----------|
| 2026-01-16 | Projet initie, brainstorming |
| 2026-01-20 | Planification terminee (PRD, Architecture, Stories) |
| 2026-01-21 | Sprint 1 termine (Setup + Auth) |
| 2026-01-21 | Sprint 2 termine (Parametres) |
| 2026-01-22 | Sprint 3 termine (Clients - CRUD, contacts, notes, liens) |
