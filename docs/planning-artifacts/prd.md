# PRD - Factu

**Version:** 1.0
**Date:** 2026-01-20
**Statut:** Validé

---

## 1. Introduction

### 1.1 Objectif du document
Ce document décrit les spécifications fonctionnelles détaillées de l'application Factu V1.

### 1.2 Documents de référence
- [Product Brief](./product-brief.md)
- [Brainstorming Session](../analysis/brainstorming-session-2026-01-16.md)

### 1.3 Glossaire
| Terme | Définition |
|-------|------------|
| Client | Entreprise cliente achetant des licences logicielles |
| Contrat | Accord commercial définissant les modules, tarifs et périodicité |
| Instance | Environnement logiciel déployé (nom technique) |
| Module | Fonctionnalité logicielle vendue (ex: Paie, Congés, etc.) |
| Relevé | Extraction du nombre de licences à une date donnée |
| Périodicité | Fréquence de facturation (Mensuel, Trimestriel, Annuel) |

---

## 2. Architecture fonctionnelle

### 2.1 Vue d'ensemble des modules

```
┌─────────────────────────────────────────────────────────────┐
│                         FACTU                                │
├─────────────┬─────────────┬─────────────┬──────────────────┤
│  Dashboard  │   Clients   │  Contrats   │    Licences      │
├─────────────┼─────────────┴─────────────┼──────────────────┤
│             │        Facturation        │   Paramètres     │
└─────────────┴───────────────────────────┴──────────────────┘
```

### 2.2 Navigation principale
| Menu | Sous-menus |
|------|------------|
| Dashboard | - |
| Clients | Liste, Fiche client |
| Contrats | Liste, Fiche contrat |
| Licences | Import, Traitement relevés, Mapping instances |
| Facturation | Workflow, Liste factures |
| Paramètres | Émetteur, Modules, CGV, Facturation, Utilisateurs |

---

## 3. Spécifications fonctionnelles

---

### 3.1 Dashboard

#### 3.1.1 Description
Page d'accueil affichant les indicateurs clés de l'activité.

#### 3.1.2 Maquette textuelle
```
┌─────────────────────────────────────────────────────────────┐
│  DASHBOARD                                                   │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐          │
│  │ CLIENTS     │  │ MOIS PRÉC.  │  │ MOIS EN     │          │
│  │ ACTIFS      │  │             │  │ COURS       │          │
│  │             │  │ CA: XXX €   │  │ CA: XXX €   │          │
│  │    342      │  │ Fact: 298   │  │ Fact: 156   │          │
│  └─────────────┘  └─────────────┘  └─────────────┘          │
│                                                              │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐          │
│  │ CA ANNÉE    │  │ CA ANNÉE    │  │ EN ATTENTE  │          │
│  │ EN COURS    │  │ PASSÉE      │  │ PAIEMENT    │          │
│  │             │  │             │  │             │          │
│  │ XXX XXX €   │  │ XXX XXX €   │  │ XX XXX €    │          │
│  └─────────────┘  └─────────────┘  └─────────────┘          │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

#### 3.1.3 Indicateurs

| Indicateur | Calcul | Format |
|------------|--------|--------|
| Clients actifs | COUNT(clients WHERE statut = 'actif') | Nombre entier |
| CA mois précédent | SUM(factures.montant_ttc WHERE mois = M-1) | Montant € |
| Factures mois précédent | COUNT(factures WHERE mois = M-1) | Nombre entier |
| CA mois en cours | SUM(factures.montant_ttc WHERE mois = M) | Montant € |
| Factures mois en cours | COUNT(factures WHERE mois = M) | Nombre entier |
| CA année en cours | SUM(factures.montant_ttc WHERE année = N) | Montant € |
| CA année passée | SUM(factures.montant_ttc WHERE année = N-1) | Montant € |
| En attente paiement | SUM(factures.montant_ttc WHERE statut != 'payée') | Montant € |

#### 3.1.4 User Stories
| ID | Story |
|----|-------|
| DASH-01 | En tant qu'utilisateur, je veux voir le nombre de clients actifs pour connaître la taille de mon portefeuille |
| DASH-02 | En tant qu'utilisateur, je veux voir le CA du mois en cours et précédent pour suivre l'activité |
| DASH-03 | En tant qu'utilisateur, je veux voir le CA cumulé annuel pour comparer avec l'année passée |
| DASH-04 | En tant qu'utilisateur, je veux voir le montant en attente de paiement pour suivre la trésorerie |

---

### 3.2 Gestion des Clients

#### 3.2.1 Liste des clients

##### Description
Liste paginée de tous les clients avec recherche et filtres.

##### Maquette textuelle
```
┌─────────────────────────────────────────────────────────────┐
│  CLIENTS                                      [+ Nouveau]   │
├─────────────────────────────────────────────────────────────┤
│  🔍 [Rechercher...                    ]  ☐ Masquer inactifs │
│                                                              │
│  [Exporter CSV]                                              │
├─────────────────────────────────────────────────────────────┤
│  NOM CLIENT        │ SIREN     │ LICENCES │ STATUT          │
├────────────────────┼───────────┼──────────┼─────────────────┤
│  ACME Corp         │ 123456789 │ 150      │ ● Actif         │
│  Beta Industries   │ 987654321 │ 75       │ ● Actif         │
│  Gamma SA          │ 456789123 │ 0        │ ○ Inactif       │
│  ...               │           │          │                 │
├─────────────────────────────────────────────────────────────┤
│  < 1 2 3 ... 20 >                          400 clients      │
└─────────────────────────────────────────────────────────────┘
```

##### Colonnes
| Colonne | Type | Tri | Description |
|---------|------|-----|-------------|
| Nom client | Texte (lien) | Oui | Clic → Fiche client |
| SIREN | Texte | Oui | 9 chiffres |
| Licences | Nombre | Oui | Total licences en cours |
| Statut | Badge | Oui | Actif / Inactif |

##### Fonctionnalités
| Fonction | Description |
|----------|-------------|
| Recherche | Recherche sur nom client et SIREN |
| Filtre inactifs | Toggle pour masquer/afficher les clients inactifs |
| Export CSV | Télécharge la liste filtrée en CSV |
| Pagination | 20 éléments par page |
| Tri | Clic sur en-tête de colonne |

##### User Stories
| ID | Story |
|----|-------|
| CLI-01 | En tant qu'utilisateur, je veux voir la liste de tous les clients |
| CLI-02 | En tant qu'utilisateur, je veux rechercher un client par nom ou SIREN |
| CLI-03 | En tant qu'utilisateur, je veux filtrer les clients inactifs |
| CLI-04 | En tant qu'utilisateur, je veux exporter la liste en CSV |
| CLI-05 | En tant qu'utilisateur, je veux accéder à la fiche d'un client en cliquant sur son nom |

---

#### 3.2.2 Fiche Client 360°

##### Description
Vue complète d'un client avec toutes ses informations, contrats et historique.

##### Maquette textuelle
```
┌─────────────────────────────────────────────────────────────┐
│  < Retour liste    CLIENT: ACME Corp           [Modifier]   │
├─────────────────────────────────────────────────────────────┤
│  INFORMATIONS GÉNÉRALES                        Statut: ●    │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ Nom:        ACME Corp                                  │ │
│  │ SIREN:      123 456 789  ✓                             │ │
│  │ N° TVA:     FR12123456789                              │ │
│  │ Adresse:    15 rue de la Paix, 75001 Paris             │ │
│  │ RIB:        FR76 1234 5678 9012 3456 7890 123  ✓       │ │
│  └────────────────────────────────────────────────────────┘ │
│                                                              │
│  CONTACTS                                      [+ Ajouter]   │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ • Jean Dupont - jean@acme.com - 01 23 45 67 89         │ │
│  │   Note: Contact principal facturation                   │ │
│  │ • Marie Martin - marie@acme.com - 01 98 76 54 32       │ │
│  └────────────────────────────────────────────────────────┘ │
│                                                              │
│  CONTRATS                                                    │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ INSTANCE        │ DATE SIGN. │ LICENCES │ ACTION       │ │
│  │ ACME-PROD       │ 15/03/2024 │ 150      │ [Voir]       │ │
│  └────────────────────────────────────────────────────────┘ │
│                                                              │
│  FACTURES                                    [Voir toutes]   │
│                                                              │
│  LIENS CLIENTS                                 [+ Ajouter]   │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ • ACME Legacy (fusion 01/2025) - "Ancienne entité"     │ │
│  └────────────────────────────────────────────────────────┘ │
│                                                              │
│  NOTES                                         [+ Ajouter]   │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ 15/01/2026 10:32 - Client VIP, attention particulière  │ │
│  │ 03/12/2025 14:15 - Changement de contact facturation   │ │
│  └────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

##### Champs - Informations générales
| Champ | Type | Obligatoire | Validation |
|-------|------|-------------|------------|
| Nom | Texte | Oui | Max 255 caractères |
| SIREN | Texte | Oui | 9 chiffres, contrôle de validité |
| N° TVA | Texte | Non | Format FR + 11 caractères |
| Adresse facturation | Texte multiligne | Oui | - |
| RIB (IBAN) | Texte | Non | Contrôle clé IBAN |
| Statut | Select | Oui | Actif / Inactif |

##### Champs - Contact
| Champ | Type | Obligatoire |
|-------|------|-------------|
| Nom | Texte | Oui |
| Prénom | Texte | Oui |
| Téléphone | Texte | Non |
| Email | Email | Oui |
| Note | Texte | Non |

##### Sections
| Section | Contenu |
|---------|---------|
| Contrats | Liste des contrats liés (instance, date signature, nb licences) |
| Factures | Lien vers liste factures filtrée |
| Liens clients | Clients liés (fusion/scission) avec commentaire |
| Notes | Notes horodatées avec historique |

##### Règles de gestion
| ID | Règle |
|----|-------|
| CLI-RG-01 | Le SIREN doit être validé (algorithme de Luhn) |
| CLI-RG-02 | L'IBAN doit être validé (clé de contrôle) |
| CLI-RG-03 | Un client peut avoir plusieurs contacts |
| CLI-RG-04 | Les notes sont horodatées automatiquement (non modifiables) |
| CLI-RG-05 | Un client inactif ne peut pas avoir de nouveau contrat |

##### User Stories
| ID | Story |
|----|-------|
| CLI-10 | En tant qu'utilisateur, je veux voir toutes les informations d'un client |
| CLI-11 | En tant qu'utilisateur, je veux modifier les informations d'un client |
| CLI-12 | En tant qu'utilisateur, je veux ajouter/modifier/supprimer des contacts |
| CLI-13 | En tant qu'utilisateur, je veux voir les contrats liés au client |
| CLI-14 | En tant qu'utilisateur, je veux accéder aux factures du client |
| CLI-15 | En tant qu'utilisateur, je veux créer un lien vers un autre client |
| CLI-16 | En tant qu'utilisateur, je veux ajouter des notes horodatées |
| CLI-17 | En tant qu'utilisateur, je veux être alerté si le SIREN est invalide |
| CLI-18 | En tant qu'utilisateur, je veux être alerté si l'IBAN est invalide |

---

### 3.3 Gestion des Contrats

#### 3.3.1 Liste des contrats

##### Description
Liste paginée de tous les contrats avec recherche et filtres.

##### Maquette textuelle
```
┌─────────────────────────────────────────────────────────────┐
│  CONTRATS                                     [+ Nouveau]   │
├─────────────────────────────────────────────────────────────┤
│  🔍 [Rechercher...                    ]  ☐ Masquer résiliés │
│                                                              │
│  [Exporter CSV]                                              │
├─────────────────────────────────────────────────────────────┤
│  CLIENT     │INSTANCE  │SIGN.    │ANNIV.  │PERIOD.│LIC.│STAT│
├─────────────┼──────────┼─────────┼────────┼───────┼────┼────┤
│  ACME Corp  │ACME-PROD │15/03/24 │15/03   │Mens.  │150 │ ●  │
│  Beta Ind.  │BETA-01   │01/06/23 │01/06   │Trim.  │75  │ ●  │
│  ...        │          │         │        │       │    │    │
├─────────────────────────────────────────────────────────────┤
│  < 1 2 3 ... 15 >                          380 contrats     │
└─────────────────────────────────────────────────────────────┘
```

##### Colonnes
| Colonne | Type | Tri | Description |
|---------|------|-----|-------------|
| Client | Texte (lien) | Oui | Clic → Fiche client |
| Instance | Texte (lien) | Oui | Clic → Fiche contrat |
| Date signature | Date | Oui | Format JJ/MM/AA |
| Date anniversaire | Date | Oui | Format JJ/MM |
| Périodicité | Badge | Oui | Mens. / Trim. / Ann. |
| Licences | Nombre | Oui | Nb licences en cours |
| Statut | Badge | Oui | Actif / Résilié |

##### User Stories
| ID | Story |
|----|-------|
| CTR-01 | En tant qu'utilisateur, je veux voir la liste de tous les contrats |
| CTR-02 | En tant qu'utilisateur, je veux rechercher un contrat par client ou instance |
| CTR-03 | En tant qu'utilisateur, je veux filtrer les contrats résiliés |
| CTR-04 | En tant qu'utilisateur, je veux exporter la liste en CSV |

---

#### 3.3.2 Fiche Contrat

##### Description
Vue complète d'un contrat avec lignes tarifaires, événements et historique.

##### Maquette textuelle
```
┌─────────────────────────────────────────────────────────────┐
│  < Retour liste    CONTRAT: ACME-PROD          [Modifier]   │
├─────────────────────────────────────────────────────────────┤
│  INFORMATIONS GÉNÉRALES                        Statut: ●    │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ Client:        ACME Corp [lien]                        │ │
│  │ Instance:      ACME-PROD                               │ │
│  │ Date sign.:    15/03/2024                              │ │
│  │ Date anniv.:   15/03                                   │ │
│  │ Périodicité:   Mensuel                                 │ │
│  │ ☐ Facture particulière                                 │ │
│  └────────────────────────────────────────────────────────┘ │
│                                                              │
│  LIGNES TARIFAIRES                             [+ Ajouter]   │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ MODULE              │ QTÉ │ PRIX U. │ REMISE │ TOTAL   │ │
│  │ Module Paie         │ 150 │ 0,50 €  │ -      │ 75,00 € │ │
│  │ Module Congés       │ 150 │ 2,03 €  │ 10%    │ 274,05 €│ │
│  │ Module Temps        │ 150 │ 1,50 €  │ -      │ 225,00 €│ │
│  │─────────────────────┼─────┼─────────┼────────┼─────────│ │
│  │ TOTAL HT            │     │         │        │ 574,05 €│ │
│  └────────────────────────────────────────────────────────┘ │
│                                                              │
│  ÉVOLUTION LICENCES                                          │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ Licences actuelles: 150                                │ │
│  │ [Graphique courbe 12 derniers mois]                    │ │
│  │  160 ┤                    ╭─╮                          │ │
│  │  140 ┤    ╭──────────────╯   ╰──────                   │ │
│  │  120 ┤────╯                                            │ │
│  │      └─────────────────────────────────                │ │
│  │       J F M A M J J A S O N D                          │ │
│  └────────────────────────────────────────────────────────┘ │
│                                                              │
│  CGV                                           [+ Ajouter]   │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ • CGV_v2.1.pdf (01/01/2025) - Actuel      [Télécharger]│ │
│  │ • CGV_v2.0.pdf (01/01/2024) - Archivé     [Télécharger]│ │
│  └────────────────────────────────────────────────────────┘ │
│                                                              │
│  ÉVÉNEMENTS                                    [+ Ajouter]   │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ 10/01/2026 - Ajout module - Module Temps ajouté        │ │
│  │ 15/09/2025 - Changement tarif - Module Congés 2,03€    │ │
│  │ 15/03/2024 - Création - Contrat signé                  │ │
│  └────────────────────────────────────────────────────────┘ │
│                                                              │
│  FICHIERS                                      [+ Ajouter]   │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ • Contrat_signe.pdf (15/03/2024)          [Télécharger]│ │
│  │ • Avenant_1.pdf (10/01/2026)              [Télécharger]│ │
│  └────────────────────────────────────────────────────────┘ │
│                                                              │
│  RELEVÉS EN ATTENTE                                          │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ ⚠ Relevé du 15/01/2026: 155 licences      [Appliquer]  │ │
│  └────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

##### Champs - Informations générales
| Champ | Type | Obligatoire | Description |
|-------|------|-------------|-------------|
| Client | Select (lien) | Oui | Client associé |
| Instance | Texte | Oui | Nom de l'instance |
| Date signature | Date | Oui | Date de signature du contrat |
| Date anniversaire | Date | Oui | Date de renouvellement |
| Périodicité | Select | Oui | Mensuel / Trimestriel / Annuel |
| Facture particulière | Checkbox | Non | Flag pour alerte à la facturation |
| Statut | Select | Oui | Actif / Résilié |

##### Champs - Ligne tarifaire
| Champ | Type | Obligatoire |
|-------|------|-------------|
| Module | Select | Oui |
| Quantité (licences) | Nombre | Oui |
| Prix unitaire HT | Décimal | Oui |
| Remise (%) | Décimal | Non |
| TVA (%) | Décimal | Oui (défaut 20%) |

##### Types d'événements
| Type | Description |
|------|-------------|
| Ajout module | Nouveau module ajouté au contrat |
| Suppression module | Module retiré du contrat |
| Changement tarif | Modification du prix unitaire |
| Changement périodicité | Modification de la périodicité |
| Autres | Événement libre |

##### Règles de gestion
| ID | Règle |
|----|-------|
| CTR-RG-01 | Un contrat est lié à un seul client |
| CTR-RG-02 | Un contrat peut avoir plusieurs lignes tarifaires |
| CTR-RG-03 | Les modifications de tarif sont historisées avec date |
| CTR-RG-04 | Les fichiers acceptés sont uniquement PDF |
| CTR-RG-05 | La courbe d'évolution affiche les 12 derniers mois |
| CTR-RG-06 | Un relevé en attente peut être appliqué pour mettre à jour les licences |

##### User Stories
| ID | Story |
|----|-------|
| CTR-10 | En tant qu'utilisateur, je veux voir toutes les informations d'un contrat |
| CTR-11 | En tant qu'utilisateur, je veux modifier les informations d'un contrat |
| CTR-12 | En tant qu'utilisateur, je veux ajouter/modifier/supprimer des lignes tarifaires |
| CTR-13 | En tant qu'utilisateur, je veux voir l'évolution des licences sur 12 mois |
| CTR-14 | En tant qu'utilisateur, je veux attacher des CGV versionnées |
| CTR-15 | En tant qu'utilisateur, je veux ajouter des événements |
| CTR-16 | En tant qu'utilisateur, je veux attacher des fichiers PDF |
| CTR-17 | En tant qu'utilisateur, je veux voir et appliquer les relevés en attente |
| CTR-18 | En tant qu'utilisateur, je veux marquer un contrat comme "facture particulière" |

---

### 3.4 Gestion des Licences

#### 3.4.1 Import des relevés

##### Description
Interface d'import de fichiers CSV contenant les relevés de licences.

##### Maquette textuelle - Étape 1: Upload
```
┌─────────────────────────────────────────────────────────────┐
│  IMPORT LICENCES                                             │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌────────────────────────────────────────────────────────┐ │
│  │                                                        │ │
│  │     📁 Glissez votre fichier CSV ici                  │ │
│  │        ou [Parcourir...]                               │ │
│  │                                                        │ │
│  │     Format attendu:                                    │ │
│  │     instance_name,nb_licences_total,date_releve        │ │
│  │                                                        │ │
│  └────────────────────────────────────────────────────────┘ │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

##### Maquette textuelle - Étape 2: Prévisualisation
```
┌─────────────────────────────────────────────────────────────┐
│  IMPORT LICENCES - Prévisualisation                          │
├─────────────────────────────────────────────────────────────┤
│  Fichier: releve_janvier_2026.csv                            │
│  Lignes: 45                                                  │
│  ✓ Format validé                                             │
├─────────────────────────────────────────────────────────────┤
│  INSTANCE        │ DATE RELEVÉ │ LICENCES │ STATUT          │
├──────────────────┼─────────────┼──────────┼─────────────────┤
│  ACME-PROD       │ 15/01/2026  │ 155      │ ✓ OK            │
│  BETA-01         │ 15/01/2026  │ 78       │ ✓ OK            │
│  UNKNOWN-X       │ 15/01/2026  │ 30       │ ⚠ Instance ?    │
│  ...             │             │          │                 │
├─────────────────────────────────────────────────────────────┤
│  45 lignes | 43 OK | 2 avertissements | 0 erreurs           │
│                                                              │
│  [Annuler]                              [Importer]           │
└─────────────────────────────────────────────────────────────┘
```

##### Maquette textuelle - Étape 2bis: Erreurs
```
┌─────────────────────────────────────────────────────────────┐
│  IMPORT LICENCES - Erreurs détectées                         │
├─────────────────────────────────────────────────────────────┤
│  Fichier: releve_janvier_2026.csv                            │
│  ✗ Erreurs à corriger                                        │
├─────────────────────────────────────────────────────────────┤
│  LIGNE │ ERREUR                                              │
├────────┼────────────────────────────────────────────────────┤
│  3     │ Colonne 'nb_licences_total' manquante              │
│  15    │ Date invalide: '2026-13-01'                        │
│  28    │ Nombre de licences non numérique: 'abc'            │
├─────────────────────────────────────────────────────────────┤
│  Import impossible. Veuillez corriger le fichier.           │
│                                                              │
│  [Annuler]                              [Réessayer]          │
└─────────────────────────────────────────────────────────────┘
```

##### Format CSV attendu
```csv
instance_name,nb_licences_total,date_releve
ACME-PROD,155,2026-01-15
BETA-01,78,2026-01-15
```

| Colonne | Type | Obligatoire | Description |
|---------|------|-------------|-------------|
| instance_name | Texte | Oui | Nom de l'instance |
| nb_licences_total | Entier | Oui | Nombre total de licences |
| date_releve | Date (YYYY-MM-DD) | Oui | Date du relevé |

##### Contrôles à l'import
| Contrôle | Type | Action |
|----------|------|--------|
| Format fichier | Bloquant | Refus si pas CSV |
| Colonnes requises | Bloquant | Refus si colonnes manquantes |
| Format date | Bloquant | Refus si date invalide |
| Nb licences numérique | Bloquant | Refus si non numérique |
| Instance inconnue | Avertissement | Import possible, signalé |

##### Règles de gestion
| ID | Règle |
|----|-------|
| LIC-RG-01 | Le fichier CSV source est archivé avec le relevé |
| LIC-RG-02 | L'import ne met pas à jour automatiquement les contrats |
| LIC-RG-03 | Les relevés sont stockés pour traitement ultérieur |
| LIC-RG-04 | Une instance inconnue génère un avertissement, pas une erreur |

##### User Stories
| ID | Story |
|----|-------|
| LIC-01 | En tant qu'utilisateur, je veux importer un fichier CSV de relevés |
| LIC-02 | En tant qu'utilisateur, je veux voir une prévisualisation avant import |
| LIC-03 | En tant qu'utilisateur, je veux voir les erreurs de format clairement |
| LIC-04 | En tant qu'utilisateur, je veux que le fichier source soit archivé |

---

#### 3.4.2 Traitement des relevés

##### Description
Interface de gestion des relevés importés en attente de traitement.

##### Maquette textuelle
```
┌─────────────────────────────────────────────────────────────┐
│  TRAITEMENT DES RELEVÉS                                      │
├─────────────────────────────────────────────────────────────┤
│  🔍 [Rechercher...           ]  Période: [Janvier 2026 ▼]   │
│                                                              │
│  [Appliquer sélection]                                       │
├─────────────────────────────────────────────────────────────┤
│  ☐ │INSTANCE   │CLIENT     │DATE     │LICENCES│DELTA│STATUT │
├───┼───────────┼───────────┼─────────┼────────┼─────┼───────┤
│  ☐ │ACME-PROD  │ACME Corp  │15/01/26 │155     │+5   │⏳ Att.│
│  ☐ │BETA-01    │Beta Ind.  │15/01/26 │78      │+3   │⏳ Att.│
│  ☐ │GAMMA-X    │Gamma SA   │15/01/26 │50      │-10  │⚠ Écart│
│  ☐ │UNKNOWN-X  │-          │15/01/26 │30      │-    │❓ ?   │
├─────────────────────────────────────────────────────────────┤
│  4 relevés | 2 en attente | 1 écart | 1 non mappé           │
└─────────────────────────────────────────────────────────────┘
```

##### Colonnes
| Colonne | Description |
|---------|-------------|
| Sélection | Checkbox pour action groupée |
| Instance | Nom de l'instance |
| Client | Client associé (si mappé) |
| Date | Date du relevé |
| Licences | Nombre de licences du relevé |
| Delta | Écart vs valeur actuelle |
| Statut | En attente / Écart significatif / Non mappé |

##### Actions
| Action | Description |
|--------|-------------|
| Appliquer | Met à jour le nb licences du contrat |
| Appliquer sélection | Action groupée sur les lignes cochées |
| Ignorer | Marque le relevé comme ignoré |

##### Règles de gestion
| ID | Règle |
|----|-------|
| LIC-RG-10 | Un écart > 10% génère une alerte visuelle |
| LIC-RG-11 | Un relevé non mappé ne peut pas être appliqué |
| LIC-RG-12 | L'application d'un relevé met à jour le contrat et historise |

##### User Stories
| ID | Story |
|----|-------|
| LIC-10 | En tant qu'utilisateur, je veux voir tous les relevés en attente |
| LIC-11 | En tant qu'utilisateur, je veux voir l'écart avec la valeur actuelle |
| LIC-12 | En tant qu'utilisateur, je veux appliquer un relevé au contrat |
| LIC-13 | En tant qu'utilisateur, je veux appliquer plusieurs relevés en masse |
| LIC-14 | En tant qu'utilisateur, je veux être alerté des écarts significatifs |

---

#### 3.4.3 Mapping des instances

##### Description
Interface de gestion des associations entre instances et clients.

##### Maquette textuelle
```
┌─────────────────────────────────────────────────────────────┐
│  MAPPING INSTANCES                                           │
├─────────────────────────────────────────────────────────────┤
│  🔍 [Rechercher...                    ]                      │
├─────────────────────────────────────────────────────────────┤
│  INSTANCE      │ ANCIENS NOMS    │ CLIENT(S)      │ ACTION  │
├────────────────┼─────────────────┼────────────────┼─────────┤
│  ACME-PROD     │ ACME-V1, ACME-2 │ ACME Corp      │[Modif.] │
│  BETA-01       │ -               │ Beta Ind.      │[Modif.] │
│  SHARED-01     │ -               │ Client A (60%) │[Modif.] │
│                │                 │ Client B (40%) │         │
│  UNKNOWN-X     │ -               │ ❓ Non mappé   │[Mapper] │
├─────────────────────────────────────────────────────────────┤
│  125 instances | 2 non mappées                               │
└─────────────────────────────────────────────────────────────┘
```

##### Modale - Mapper une instance
```
┌─────────────────────────────────────────────────────────────┐
│  MAPPER L'INSTANCE: UNKNOWN-X                    [X]        │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  Client(s) associé(s):                                       │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ Client: [Rechercher client...              ▼]          │ │
│  │ Part:   [100] %                                        │ │
│  │                                         [+ Ajouter]    │ │
│  └────────────────────────────────────────────────────────┘ │
│                                                              │
│  Historique des noms:                                        │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ Nom actuel: UNKNOWN-X                                  │ │
│  │ Anciens noms: (aucun)                                  │ │
│  │                                      [+ Ancien nom]    │ │
│  └────────────────────────────────────────────────────────┘ │
│                                                              │
│  [Annuler]                              [Enregistrer]        │
└─────────────────────────────────────────────────────────────┘
```

##### Règles de gestion
| ID | Règle |
|----|-------|
| LIC-RG-20 | Une instance peut être associée à plusieurs clients (avec %) |
| LIC-RG-21 | La somme des parts doit faire 100% |
| LIC-RG-22 | L'historique des noms est conservé |
| LIC-RG-23 | Un ancien nom peut être utilisé pour matcher à l'import |

##### User Stories
| ID | Story |
|----|-------|
| LIC-20 | En tant qu'utilisateur, je veux voir toutes les instances |
| LIC-21 | En tant qu'utilisateur, je veux mapper une instance à un client |
| LIC-22 | En tant qu'utilisateur, je veux mapper une instance à plusieurs clients |
| LIC-23 | En tant qu'utilisateur, je veux voir l'historique des noms d'instance |
| LIC-24 | En tant qu'utilisateur, je veux ajouter un ancien nom à une instance |

---

### 3.5 Facturation

#### 3.5.1 Workflow de facturation

##### Description
Interface principale de gestion des factures avec workflow en 3 colonnes.

##### Maquette textuelle
```
┌─────────────────────────────────────────────────────────────────────────────┐
│  FACTURATION                                         [Calculer les factures]│
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  À CRÉER (45)          │ BROUILLONS (12)       │ VALIDÉES (156)             │
│  ──────────────────────┼───────────────────────┼────────────────────────────│
│  ┌──────────────────┐  │ ┌──────────────────┐  │ ┌──────────────────┐       │
│  │ ACME Corp        │  │ │ ACME Corp        │  │ │ ACME Corp        │       │
│  │ 574,05 € HT      │  │ │ N° DRAFT-001     │  │ │ N° FAC-2026-001  │       │
│  │ Janv. 2026       │  │ │ 574,05 € HT      │  │ │ 574,05 € HT      │       │
│  │ ⚠ Écart licences │  │ │ Janv. 2026       │  │ │ 📧 Envoyée 15/01 │       │
│  │ [Créer]          │  │ │ [PDF] [Valider]  │  │ │ [PDF] [Renvoyer] │       │
│  │                  │  │ │ [Supprimer]      │  │ │                  │       │
│  └──────────────────┘  │ └──────────────────┘  │ └──────────────────┘       │
│  ┌──────────────────┐  │ ┌──────────────────┐  │ ┌──────────────────┐       │
│  │ Beta Industries  │  │ │ Delta SA         │  │ │ Epsilon SAS      │       │
│  │ 225,00 € HT      │  │ │ N° DRAFT-002     │  │ │ N° FAC-2026-002  │       │
│  │ Janv. 2026       │  │ │ 1 250,00 € HT    │  │ │ 890,00 € HT      │       │
│  │ 🔔 Fact. partic. │  │ │ Janv. 2026       │  │ │ ⏳ Non envoyée   │       │
│  │ [Créer]          │  │ │ [PDF] [Valider]  │  │ │ [PDF] [Envoyer]  │       │
│  │                  │  │ │ [Supprimer]      │  │ │ [→ Brouillon]    │       │
│  └──────────────────┘  │ └──────────────────┘  │ └──────────────────┘       │
│  ...                   │ ...                   │ ...                        │
│                        │                       │                            │
└─────────────────────────────────────────────────────────────────────────────┘
```

##### Colonne "À créer"
| Élément | Description |
|---------|-------------|
| Alimentation | Bouton "Calculer les factures" |
| Calcul | Basé sur périodicité et date anniversaire |
| Affichage | Client, Montant HT estimé, Période |
| Alertes | Écart licences, Facture particulière |
| Action | Bouton "Créer" |

##### Colonne "Brouillons"
| Élément | Description |
|---------|-------------|
| Contenu | Factures créées non validées |
| Numéro | Numéro temporaire (DRAFT-XXX) |
| Modification | Possible |
| Actions | Voir PDF, Valider, Supprimer (→ À créer) |

##### Colonne "Validées"
| Élément | Description |
|---------|-------------|
| Contenu | Factures validées avec numéro définitif |
| Numéro | Numéro séquentiel définitif |
| Modification | Impossible |
| Statut envoi | Non envoyée / Envoyée (date/heure) |
| Actions | Voir PDF, Envoyer/Renvoyer, → Brouillon (si non envoyée) |

##### Règles de gestion
| ID | Règle |
|----|-------|
| FAC-RG-01 | Le calcul des factures se base sur la périodicité et date anniversaire |
| FAC-RG-02 | Une alerte "écart licences" s'affiche si delta > 10% |
| FAC-RG-03 | Une alerte "facture particulière" s'affiche si flag activé sur contrat |
| FAC-RG-04 | Un brouillon supprimé retourne dans "À créer" |
| FAC-RG-05 | Une facture validée reçoit un numéro définitif séquentiel |
| FAC-RG-06 | Une facture validée non envoyée peut retourner en brouillon |
| FAC-RG-07 | Une facture envoyée ne peut plus être modifiée |
| FAC-RG-08 | L'envoi est horodaté (date + heure) |

##### User Stories
| ID | Story |
|----|-------|
| FAC-01 | En tant qu'utilisateur, je veux calculer les factures à émettre |
| FAC-02 | En tant qu'utilisateur, je veux voir les alertes sur les factures |
| FAC-03 | En tant qu'utilisateur, je veux créer une facture (brouillon) |
| FAC-04 | En tant qu'utilisateur, je veux visualiser le PDF d'un brouillon |
| FAC-05 | En tant qu'utilisateur, je veux valider un brouillon |
| FAC-06 | En tant qu'utilisateur, je veux supprimer un brouillon |
| FAC-07 | En tant qu'utilisateur, je veux envoyer une facture par email |
| FAC-08 | En tant qu'utilisateur, je veux voir la date/heure d'envoi |
| FAC-09 | En tant qu'utilisateur, je veux repasser en brouillon (si non envoyée) |

---

#### 3.5.2 Liste des factures

##### Description
Historique de toutes les factures avec recherche et filtres.

##### Maquette textuelle
```
┌─────────────────────────────────────────────────────────────┐
│  LISTE DES FACTURES                                          │
├─────────────────────────────────────────────────────────────┤
│  🔍 [Rechercher...    ]  Période: [Janvier 2026 ▼]          │
│  Client: [Tous ▼]        Statut: [Tous ▼]                   │
│                                                              │
│  [Exporter CSV]                                              │
├─────────────────────────────────────────────────────────────┤
│  NUMÉRO      │CLIENT     │ÉMISSION │ÉCHÉANCE│MONTANT │STATUT│
├──────────────┼───────────┼─────────┼────────┼────────┼──────┤
│  FAC-2026-001│ACME Corp  │15/01/26 │14/02/26│688,86 €│📧 Env│
│  FAC-2026-002│Beta Ind.  │15/01/26 │14/02/26│270,00 €│⏳ Val│
│  DRAFT-003   │Delta SA   │-        │-       │1500,00€│📝 Bro│
│  ...         │           │         │        │        │      │
├─────────────────────────────────────────────────────────────┤
│  < 1 2 3 ... 50 >                         1250 factures     │
└─────────────────────────────────────────────────────────────┘
```

##### Colonnes
| Colonne | Type | Tri | Description |
|---------|------|-----|-------------|
| Numéro | Texte (lien) | Oui | Clic → Détail facture |
| Client | Texte (lien) | Oui | Clic → Fiche client |
| Date émission | Date | Oui | - |
| Date échéance | Date | Oui | - |
| Montant TTC | Montant | Oui | - |
| Statut | Badge | Oui | Brouillon / Validée / Envoyée / Payée |

##### Filtres
| Filtre | Type | Options |
|--------|------|---------|
| Recherche | Texte | Sur numéro, client |
| Période | Select | Mois/Année |
| Client | Select | Liste clients |
| Statut | Select | Tous / Brouillon / Validée / Envoyée / Payée |

##### User Stories
| ID | Story |
|----|-------|
| FAC-20 | En tant qu'utilisateur, je veux voir l'historique des factures |
| FAC-21 | En tant qu'utilisateur, je veux filtrer par période |
| FAC-22 | En tant qu'utilisateur, je veux filtrer par client |
| FAC-23 | En tant qu'utilisateur, je veux filtrer par statut |
| FAC-24 | En tant qu'utilisateur, je veux exporter en CSV |
| FAC-25 | En tant qu'utilisateur, je veux accéder à la fiche client depuis la liste |

---

#### 3.5.3 Format de la facture PDF

##### Structure
```
┌─────────────────────────────────────────────────────────────┐
│  [LOGO]                          ÉMETTEUR                   │
│                                  Raison sociale             │
│                                  Adresse                    │
│  FACTURE                         Email / Tél                │
│  Numéro: FAC-2026-00001         SIREN / TVA                │
│  Date: 15/01/2026                                           │
│  Échéance: 14/02/2026            CLIENT                     │
│                                  Raison sociale             │
│                                  Adresse                    │
│                                  SIREN / TVA                │
├─────────────────────────────────────────────────────────────┤
│  MODULE          │ QTÉ  │ P.U. HT │ REM. │ TVA  │ TOTAL HT │
├──────────────────┼──────┼─────────┼──────┼──────┼──────────┤
│  Module Paie     │ 150  │ 0,50 €  │ -    │ 20%  │ 75,00 €  │
│  Module Congés   │ 150  │ 2,03 €  │ 10%  │ 20%  │ 274,05 € │
│  Module Temps    │ 150  │ 1,50 €  │ -    │ 20%  │ 225,00 € │
├─────────────────────────────────────────────────────────────┤
│                                  Total HT avant remise: XXX │
│                                  Remise globale: X%         │
│                                  Total HT: 574,05 €         │
│                                  TVA 20%: 114,81 €          │
│                                  TOTAL TTC: 688,86 €        │
├─────────────────────────────────────────────────────────────┤
│  PAIEMENT                                                   │
│  Moyen: Virement                                            │
│  IBAN: FRXX XXXX XXXX XXXX XXXX XXXX XXX                   │
│  BIC: XXXXXXXXX                                             │
├─────────────────────────────────────────────────────────────┤
│  Mentions légales:                                          │
│  - Pénalités de retard: 3x taux légal                      │
│  - Indemnité forfaitaire: 40 €                             │
├─────────────────────────────────────────────────────────────┤
│  [Infos société: forme juridique, capital, SIREN, TVA]     │
└─────────────────────────────────────────────────────────────┘
```

---

### 3.6 Paramètres

#### 3.6.1 Informations Émetteur

##### Champs
| Champ | Type | Obligatoire |
|-------|------|-------------|
| Raison sociale | Texte | Oui |
| Forme juridique | Select (SAS, SARL, SA, etc.) | Oui |
| Capital social | Montant | Oui |
| Adresse | Texte multiligne | Oui |
| SIREN | Texte (9 chiffres) | Oui |
| N° TVA | Texte | Oui |
| Email | Email | Oui |
| Téléphone | Texte | Oui |
| IBAN | Texte | Oui |
| BIC | Texte | Oui |
| Logo | Image (PNG, JPG) | Non |

##### User Stories
| ID | Story |
|----|-------|
| PAR-01 | En tant qu'admin, je veux configurer les informations de l'émetteur |
| PAR-02 | En tant qu'admin, je veux uploader le logo pour les factures |

---

#### 3.6.2 Catalogue Modules

##### Maquette textuelle
```
┌─────────────────────────────────────────────────────────────┐
│  CATALOGUE MODULES                            [+ Nouveau]   │
├─────────────────────────────────────────────────────────────┤
│  NOM MODULE                │ PRIX DÉFAUT │ TVA  │ STATUT   │
├────────────────────────────┼─────────────┼──────┼──────────┤
│  Module Paie               │ 0,50 €      │ 20%  │ ● Actif  │
│  Module Congés & Absences  │ 2,03 €      │ 20%  │ ● Actif  │
│  Module Temps & Activités  │ 1,50 €      │ 20%  │ ● Actif  │
│  Module Notes de frais     │ 0,25 €      │ 20%  │ ○ Inactif│
│  ...                       │             │      │          │
└─────────────────────────────────────────────────────────────┘
```

##### Champs
| Champ | Type | Obligatoire |
|-------|------|-------------|
| Nom | Texte | Oui |
| Prix unitaire par défaut | Décimal | Oui |
| Taux TVA | Décimal (défaut 20%) | Oui |
| Statut | Select (Actif/Inactif) | Oui |

##### Règles de gestion
| ID | Règle |
|----|-------|
| PAR-RG-01 | Un module inactif n'apparaît plus dans les nouveaux contrats |
| PAR-RG-02 | Le prix par défaut peut être modifié au niveau du contrat |

##### User Stories
| ID | Story |
|----|-------|
| PAR-10 | En tant qu'admin, je veux gérer le catalogue des modules |
| PAR-11 | En tant qu'admin, je veux définir un prix par défaut |
| PAR-12 | En tant qu'admin, je veux désactiver un module obsolète |

---

#### 3.6.3 Gestion CGV

##### Maquette textuelle
```
┌─────────────────────────────────────────────────────────────┐
│  CONDITIONS GÉNÉRALES DE VENTE                [+ Ajouter]   │
├─────────────────────────────────────────────────────────────┤
│  VERSION    │ VALIDITÉ DU   │ VALIDITÉ AU │ DÉFAUT│ ACTION │
├─────────────┼───────────────┼─────────────┼───────┼────────┤
│  CGV v2.1   │ 01/01/2025    │ -           │ ✓     │[Téléch]│
│  CGV v2.0   │ 01/01/2024    │ 31/12/2024  │       │[Téléch]│
│  CGV v1.0   │ 01/01/2023    │ 31/12/2023  │       │[Téléch]│
└─────────────────────────────────────────────────────────────┘
```

##### Champs
| Champ | Type | Obligatoire |
|-------|------|-------------|
| Fichier PDF | Upload | Oui |
| Nom/Version | Texte | Oui |
| Date début validité | Date | Oui |
| Date fin validité | Date | Non |
| Par défaut | Checkbox | Non |

##### Règles de gestion
| ID | Règle |
|----|-------|
| PAR-RG-10 | Une seule CGV peut être "par défaut" |
| PAR-RG-11 | La CGV par défaut est proposée pour les nouveaux contrats |

##### User Stories
| ID | Story |
|----|-------|
| PAR-20 | En tant qu'admin, je veux uploader une nouvelle version de CGV |
| PAR-21 | En tant qu'admin, je veux définir la CGV par défaut |
| PAR-22 | En tant qu'utilisateur, je veux télécharger une ancienne version |

---

#### 3.6.4 Paramètres Facturation

##### Champs
| Champ | Type | Description |
|-------|------|-------------|
| Format numérotation | Texte | Ex: FAC-{YYYY}-{SEQ:5} |
| Prochain numéro | Nombre | Numéro séquentiel suivant |
| Délai échéance | Nombre | Jours (défaut 30) |
| Mentions légales | Texte multiligne | Pénalités, indemnité, etc. |
| Email expéditeur | Email | Adresse d'envoi des factures |
| Objet email | Texte | Objet par défaut |
| Corps email | Texte multiligne | Template email |

##### User Stories
| ID | Story |
|----|-------|
| PAR-30 | En tant qu'admin, je veux configurer le format de numérotation |
| PAR-31 | En tant qu'admin, je veux définir le délai d'échéance par défaut |
| PAR-32 | En tant qu'admin, je veux personnaliser les mentions légales |
| PAR-33 | En tant qu'admin, je veux configurer le template d'email |

---

#### 3.6.5 Utilisateurs

##### Maquette textuelle
```
┌─────────────────────────────────────────────────────────────┐
│  UTILISATEURS                                 [+ Nouveau]   │
├─────────────────────────────────────────────────────────────┤
│  NOM            │ EMAIL                │ RÔLE  │ STATUT    │
├─────────────────┼──────────────────────┼───────┼───────────┤
│  Jean Dupont    │ jean@kemeo.com       │ Admin │ ● Actif   │
│  Marie Martin   │ marie@kemeo.com      │ User  │ ● Actif   │
│  ...            │                      │       │           │
└─────────────────────────────────────────────────────────────┘
```

##### Champs
| Champ | Type | Obligatoire |
|-------|------|-------------|
| Nom | Texte | Oui |
| Email | Email | Oui |
| Rôle | Select | Oui (V2: Admin/User) |
| Statut | Select | Oui (Actif/Inactif) |

##### Note
La gestion des rôles et permissions est prévue pour V2. En V1, tous les utilisateurs ont accès à toutes les fonctionnalités.

##### User Stories
| ID | Story |
|----|-------|
| PAR-40 | En tant qu'admin, je veux créer un utilisateur |
| PAR-41 | En tant qu'admin, je veux désactiver un utilisateur |

---

## 4. Exigences non fonctionnelles

### 4.1 Performance
| Exigence | Cible |
|----------|-------|
| Temps de chargement page | < 2 secondes |
| Génération PDF | < 5 secondes |
| Import CSV (500 lignes) | < 10 secondes |
| Export CSV (1000 lignes) | < 5 secondes |

### 4.2 Compatibilité
| Navigateur | Version minimum |
|------------|-----------------|
| Chrome | 90+ |
| Firefox | 90+ |
| Edge | 90+ |
| Safari | 14+ |

### 4.3 Sécurité
| Exigence | Description |
|----------|-------------|
| Authentification | Login / Mot de passe |
| Sessions | Expiration après inactivité |
| HTTPS | Obligatoire |
| Données sensibles | RIB, IBAN chiffrés en base |

### 4.4 Sauvegarde
| Exigence | Description |
|----------|-------------|
| Base de données | Backup quotidien |
| Fichiers | Backup quotidien |
| Rétention | 30 jours minimum |

---

## 5. Annexes

### 5.1 Récapitulatif des User Stories

| Module | Nombre de stories |
|--------|-------------------|
| Dashboard | 4 |
| Clients | 18 |
| Contrats | 18 |
| Licences | 24 |
| Facturation | 25 |
| Paramètres | 12 |
| **TOTAL** | **101** |

### 5.2 Documents liés
- [Product Brief](./product-brief.md)
- [Brainstorming Session](../analysis/brainstorming-session-2026-01-16.md)
- Architecture (à venir)
- Epics & Stories (à venir)
