# Factu - Application de Gestion de Facturation

Application web de gestion de contrats et facturation pour editeurs de logiciels SaaS.

---

## Vue d'ensemble

**Factu** permet de gerer le cycle complet de facturation :
1. Gestion des clients et contacts
2. Creation et suivi des contrats de licence
3. Generation automatique des factures selon la periodicite
4. Edition et export des factures en PDF

---

## Architecture fonctionnelle

```
+------------------+     +------------------+     +------------------+
|    EMETTEUR      |     |     CLIENT       |     |     MODULE       |
|  (votre societe) |     | (vos clients)    |     | (vos produits)   |
+--------+---------+     +--------+---------+     +--------+---------+
         |                        |                        |
         v                        v                        v
+--------+------------------------+------------------------+---------+
|                           CONTRAT                                   |
|  - Lie un client a un emetteur                                     |
|  - Contient des lignes (modules + quantites + prix)                |
|  - Definit la periodicite de facturation                           |
+--------+-----------------------------------------------------------+
         |
         v
+--------+-----------------------------------------------------------+
|                           FACTURE                                   |
|  - Generee depuis un contrat                                       |
|  - Snapshot des donnees (client, emetteur, lignes)                 |
|  - Workflow: Brouillon -> Validee -> Envoyee -> Payee              |
+--------------------------------------------------------------------+
```

---

## Entites principales

### Emetteur
L'entite qui emet les factures (votre societe).

| Champ | Description |
|-------|-------------|
| `code` | Identifiant unique (ex: ACME) |
| `nom` | Nom d'affichage |
| `versions` | Historique des informations legales (raison sociale, adresse, SIREN, TVA, IBAN) |
| `parametreFacturation` | Configuration (prefixe facture, delai echeance, mentions legales) |

**Versions** : Les informations legales sont versionnees pour conserver l'historique en cas de changement d'adresse ou de raison sociale.

### Client
Les clients factures.

| Champ | Description |
|-------|-------------|
| `code` | Identifiant unique (ex: CLI001) |
| `raisonSociale` | Nom de l'entreprise |
| `siren` | Numero SIREN (valide) |
| `adresse` | Adresse complete |
| `email`, `telephone` | Coordonnees |
| `contacts` | Liste des contacts (nom, email, telephone) |
| `notes` | Notes internes |

### Module
Les produits/services vendus.

| Champ | Description |
|-------|-------------|
| `nom` | Nom du module (ex: Licence Standard) |
| `prixDefaut` | Prix unitaire par defaut |
| `tauxTva` | Taux de TVA applique (defaut: 20%) |
| `description` | Description du module |

### Contrat
Lie un client a un emetteur avec des conditions de facturation.

| Champ | Description |
|-------|-------------|
| `numero` | Numero unique du contrat |
| `client` | Client concerne |
| `emetteur` | Emetteur des factures |
| `instance` | Instance technique (optionnel) |
| `dateSignature` | Date de signature |
| `dateDebutFacturation` | Date de debut de facturation |
| `periodicite` | `mensuelle`, `trimestrielle`, `annuelle` |
| `lignes` | Lignes du contrat (module, quantite, prix) |
| `statut` | `actif`, `suspendu`, `resilie` |

### Facture
Document de facturation genere depuis un contrat.

| Champ | Description |
|-------|-------------|
| `numero` | Numero unique (ex: FAC-2026-0001) |
| `contrat` | Contrat source |
| `clientRaisonSociale`, `clientAdresse`, ... | Snapshot client |
| `emetteurRaisonSociale`, `emetteurAdresse`, ... | Snapshot emetteur |
| `periodeDebut`, `periodeFin` | Periode facturee |
| `lignes` | Lignes de la facture |
| `totalHt`, `totalTva`, `totalTtc` | Montants |
| `statut` | Workflow (voir ci-dessous) |

---

## Workflow de facturation

```
+-------------+     +-------------+     +-------------+     +-------------+
|  BROUILLON  | --> |   VALIDEE   | --> |   ENVOYEE   | --> |    PAYEE    |
+-------------+     +-------------+     +-------------+     +-------------+
      |                   |                   |                   |
      |                   |                   |                   |
   Modifiable          Numero            Marquee comme       Marquee comme
   Supprimable         attribue          envoyee au          reglee
                       Figee             client
```

### Etats

| Statut | Description | Actions possibles |
|--------|-------------|-------------------|
| **Brouillon** | Facture en cours de creation | Modifier, Supprimer, Valider |
| **Validee** | Facture figee avec numero | Marquer envoyee, Marquer payee, PDF |
| **Envoyee** | Facture envoyee au client | Marquer payee, PDF |
| **Payee** | Facture reglee | PDF uniquement |

### Snapshot des donnees

Lors de la creation d'une facture, les donnees du client et de l'emetteur sont **copiees** (snapshot). Cela garantit que :
- La facture reste coherente meme si le client change d'adresse
- Les informations legales de l'emetteur sont figees
- L'historique est preserve

---

## Periodicites de facturation

| Periodicite | Description | Periode facturee |
|-------------|-------------|------------------|
| **Mensuelle** | Facture chaque mois | Du 1er au dernier jour du mois |
| **Trimestrielle** | Facture chaque trimestre | 3 mois (ex: jan-mars) |
| **Annuelle** | Facture chaque annee | 12 mois depuis date anniversaire |

Le systeme detecte automatiquement les contrats a facturer en fonction de leur periodicite et de la derniere facture emise.

---

## Pages principales

### Dashboard (`/`)
Vue d'ensemble avec statistiques.

### Workflow de facturation (`/facturation`)
Vue en 3 colonnes :
1. **A creer** : Contrats a facturer ce mois
2. **Brouillons** : Factures en cours d'edition
3. **Validees** : Factures pretes a envoyer

### Liste des factures (`/facturation/liste`)
Tableau avec filtres et tri :
- Recherche par numero ou client
- Filtre par statut
- Tri par date, montant, etc.

### Detail facture (`/facturation/{id}`)
Onglets :
- **Informations** : Dates, client, emetteur, montants
- **Lignes** : Detail des lignes de facturation
- **Historique** : Transitions de statut

### Gestion des contrats (`/contrat`)
Liste et detail des contrats avec :
- Informations generales
- Lignes du contrat
- Evenements (historique)
- Fichiers attaches
- CGV associees

---

## Administration

Accessible via `/admin` (requiert `ROLE_ADMIN`).

### Modules (`/admin/modules`)
Gestion du catalogue de modules/produits.

### Emetteurs (`/admin/emetteur`)
Configuration des emetteurs :
- Informations legales (versionnees)
- Parametres de facturation (prefixe, echeance, mentions)
- CGV par defaut

### CGV (`/admin/cgv`)
Gestion des Conditions Generales de Vente :
- Upload de fichiers PDF
- Association aux emetteurs

### Clients (`/admin/clients`)
Gestion complete des clients :
- Informations
- Contacts
- Notes
- Export CSV

---

## Generation PDF

Les factures peuvent etre exportees en PDF via le bouton disponible sur :
- La page detail de la facture
- La liste des factures
- Le workflow (factures validees et brouillons)

### Contenu du PDF

```
+------------------------------------------+
|  EMETTEUR                      FACTURE   |
|  Adresse, SIREN, TVA           N° + Date |
+------------------------------------------+
|  DESTINATAIRE                            |
|  Raison sociale, adresse, SIREN          |
+------------------------------------------+
|  Periode facturee                        |
+------------------------------------------+
|  LIGNES DE FACTURE                       |
|  Designation | Qte | P.U. | TVA | Total  |
+------------------------------------------+
|  TOTAUX (HT, TVA, TTC)                   |
+------------------------------------------+
|  CONDITIONS DE PAIEMENT                  |
|  Echeance, IBAN, BIC                     |
+------------------------------------------+
|  Mentions legales                        |
+------------------------------------------+
```

Les brouillons affichent un filigrane "BROUILLON".

---

## Stack technique

| Composant | Version |
|-----------|---------|
| PHP | 8.3 |
| Symfony | 7.4 |
| Base de donnees | MySQL 8.4 |
| CSS | Tailwind CSS |
| PDF | DOMPDF 3.1 |
| JS | Alpine.js |

---

## Structure des fichiers

```
src/
├── Controller/
│   ├── Admin/           # Controllers admin (ROLE_ADMIN)
│   ├── ContratController.php
│   ├── DashboardController.php
│   ├── FacturationController.php
│   └── SecurityController.php
├── Entity/              # Entites Doctrine
├── Form/                # Formulaires
├── Repository/          # Repositories
├── Service/
│   ├── CsvExportService.php
│   ├── FacturationService.php
│   ├── FileUploadService.php
│   └── PdfFactureService.php
└── Validator/           # Validateurs custom (SIREN, IBAN)

templates/
├── admin/               # Templates admin
├── contrat/             # Templates contrats
├── facturation/         # Templates facturation
├── components/          # Composants reutilisables
└── layout.html.twig     # Layout principal

storage/
├── cgv/                 # Fichiers CGV uploades
└── contrats/            # Fichiers contrats uploades
```

---

## Conventions

### Codes

| Entite | Format | Exemple |
|--------|--------|---------|
| Client | Libre (max 20 car.) | CLI001, ACME |
| Contrat | Libre (max 50 car.) | CTR-2026-001 |
| Facture | Prefixe + annee + sequence | FAC-2026-0001 |
| Emetteur | Majuscules (max 20 car.) | MYCOMPANY |

### Montants

- Stockes en `DECIMAL(12,2)`
- Calculs avec `bcmath` (precision)
- Affichage : format francais (`1 234,56 €`)

### Dates

- Stockage : `DATE` ou `DATETIME`
- Affichage : format francais (`dd/mm/YYYY`)

---

## Securite

- Authentification par login/mot de passe
- Roles : `ROLE_USER`, `ROLE_ADMIN`
- Protection CSRF sur tous les formulaires
- Validation des SIREN et IBAN

---

## Tests

```bash
# Lancer tous les tests
php bin/phpunit

# Lancer un test specifique
php bin/phpunit --filter=NomDuTest
```

Couverture : 81 tests, 162 assertions.
