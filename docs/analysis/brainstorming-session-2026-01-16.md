---
stepsCompleted: [1, 2, 3, 4]
inputDocuments: []
session_topic: 'Fonctionnalités clés application de facturation interne'
session_goals: 'Exploration ouverte des features et contraintes métier'
selected_approach: 'AI-Recommended'
techniques_used: ['SCAMPER (partiel)', 'Context Discovery', 'Pain Points Analysis', 'Integration Mapping', 'UX Exploration']
ideas_generated: []
context_file: ''
status: 'terminé'
---

# Brainstorming Session Results

**Facilitateur:** Dylan
**Date:** 2026-01-16 (complété 2026-01-20)
**Projet:** Factu - Application de facturation interne
**Statut:** ✅ Terminé

---

## Contexte Projet

### Description
Outil de facturation **interne** pour gérer :
- Vente d'abonnements logiciels (multi-modules)
- Prestations d'accompagnement
- Base client fiable et centralisée

### Modèle commercial
| Élément | Détail |
|---------|--------|
| Produits | Logiciels avec plusieurs modules |
| Récurrence | Mensuel, Trimestriel, Annuel |
| Paiements | Virement, Prélèvement |
| Hors scope | Devis |

---

## Modèle de données identifié

```
CLIENT (historisé)
  └── CONTRAT (= devis signé)
        - Tarifs par module
        - Remises négociées
        - Périodicité
        └── LIGNE CONTRAT
              - Module souscrit
              - Prix unitaire
              - Nb licences

INSTANCE (outil vendu)
  └── Mapping vers N clients (cas rare: instance partagée)
```

### Relations complexes
- 1 Client → N Contrats
- 1 Contrat → N Modules
- 1 Instance → N Clients (rare, répartition par licences)

---

## Règles métier

| Règle | Détail |
|-------|--------|
| Prorata | ❌ Pas de prorata |
| Résiliation | À date anniversaire uniquement |
| Changement tarif | Historisé + date de prise en compte |
| Rapprochement paiements | Manuel (V2) |
| Historisation clients | Obligatoire (audit trail) |
| Fusion/Scission | Lien entre clients avec commentaire |

---

## Utilisateurs

| Aspect | Décision |
|--------|----------|
| Utilisateurs | Comptabilité + Direction |
| Nombre | 4-5 users |
| Rôles différenciés | À prévoir (V2) |
| Restrictions données | Hors scope |

---

## Pain Points identifiés

### 1. Contestation du nombre de licences
**Cause:** Pas de preuve / traçabilité
**Solutions proposées:**
- Historique relevés (date, source, fichier archivé)
- Transparence client sur le relevé utilisé
- Alertes si écart significatif vs période précédente

### 2. Méconnaissance des CGV
**Cause:** Pas d'accès facile aux termes contractuels
**Solutions proposées:**
- CGV rattachées au contrat (versionnées)
- Rappels proactifs avant date anniversaire
- FAQ intégrée

### 3. MAJ licences chronophage
**Cause:** Processus manuel d'extraction et saisie
**Solutions proposées:**
- Import fichier standardisé
- API à terme
- Alertes écarts automatiques

---

## Intégrations

| Système | V1 | V2+ | Détail |
|---------|:--:|:---:|--------|
| Outils vendus | ✅ | ✅ | Export: instance_name, nb_licences_total, date |
| Comptabilité | ❌ | ✅ | À prévoir plus tard |
| Prélèvements | ❌ | ❓ | Info à clarifier |
| Email | ✅ | ✅ | Envoi factures avec horodatage |

### Format export licences
```csv
instance_name,nb_licences_total,date_releve
ACME-PROD,100,2026-01-16
```
- Pas de découpage par module (vient du contrat)
- Pas de découpage par client (mapping dans Factu)

---

## Écrans V1

### Écran 1 : Dashboard

**Chiffres clés uniquement :**
| Indicateur |
|------------|
| Nombre de clients actifs |
| CA mois précédent + nb factures |
| CA mois en cours + nb factures |
| CA cumulé année en cours |
| CA cumulé année passée |
| Montant en attente de paiement |

---

### Écran 2 : Liste Clients

| Élément | Détail |
|---------|--------|
| **Colonnes** | Nom client (cliquable → fiche), SIREN, Nb licences en cours, Statut |
| **Statuts** | Actif / Inactif |
| **Recherche** | Barre de recherche rapide |
| **Filtres** | Toggle masquer les inactifs |
| **Export** | CSV |

---

### Écran 2b : Fiche Client 360°

**Informations générales :**
| Champ | Validation |
|-------|------------|
| Nom | |
| SIREN | ✅ Contrôle cohérence |
| Numéro TVA | |
| Adresse facturation | |
| RIB | ✅ Contrôle cohérence IBAN |
| Statut | Actif / Inactif |

**Contacts (multiples) :**
| Champ |
|-------|
| Nom |
| Prénom |
| Téléphone |
| Email |
| Note |

**Notes libres :** Horodatées avec historique

**Blocs/sections :**
| Bloc | Contenu |
|------|---------|
| Contrats | Nom instance, Date signature, Nb licences en cours |
| Factures | Lien vers liste factures filtrée sur ce client |
| Liens clients | Nom client lié + commentaire (fusion/scission) |

---

### Écran 3 : Liste Contrats

| Élément | Détail |
|---------|--------|
| **Colonnes** | Nom client, Nom instance, Date signature, Date anniversaire, Périodicité, Nb licences, Statut |
| **Recherche** | Barre de recherche rapide |
| **Filtres** | Toggle masquer inactifs/résiliés |
| **Export** | CSV |

---

### Écran 3b : Fiche Contrat

| Section | Détail |
|---------|--------|
| **Infos générales** | Client, Instance, Dates, Périodicité, Statut, Flag "Facture particulière" |
| **Lignes tarifaires** | Modules, Prix unitaire, Nb licences |
| **CGV** | PDF rattaché, versionné |
| **Historique tarifs** | Modifications avec dates |
| **Événements** | Ajout module, Suppression module, Changement tarif, Changement périodicité, Autres |
| **Fichiers** | PDF uniquement |
| **Évolution licences** | Nb actuel + Courbe 12 derniers mois |

---

### Écran 4 : Import Licences

| Étape | Action |
|-------|--------|
| Upload | Fichier CSV (instance, date relevé, nb licences) |
| Contrôle | Vérification cohérence |
| Prévisualisation | Affichage données ou erreurs |
| Stockage | Enregistrement sans MAJ auto |
| Archivage | Fichier CSV source conservé (preuve, téléchargeable) |

---

### Écran 4b : Traitement Relevés

| Accès | Fonction |
|-------|----------|
| Fiche contrat | Voir relevé en attente + Bouton "Appliquer" |
| Page dédiée | Liste des relevés à traiter + Actions par ligne/masse |

---

### Écran 4c : Mapping Instances

| Élément | Détail |
|---------|--------|
| **Accès** | Fiche contrat + Page dédiée |
| **Fonction** | Associer instance → client(s) |
| **Historisation** | Conserver l'historique des noms d'instance (changements) |

**Page dédiée :**
| Colonne |
|---------|
| Nom instance actuel |
| Ancien(s) nom(s) (historique) |
| Client(s) associé(s) |
| Nb licences |
| Actions |

---

### Écran 5 : Workflow Facturation

**3 colonnes type Kanban :**

**Colonne "À créer" :**
| Élément | Détail |
|---------|--------|
| Alimentation | Bouton "Calculer les factures" (périodicité + date anniversaire) |
| Affichage | Résumé facture + alertes (facture particulière, écart licences) |
| Action | Bouton "Créer" par ligne |

**Colonne "Créées" (Brouillon) :**
| Élément | Détail |
|---------|--------|
| Statut | Brouillon, modifiable |
| Visualisation | PDF disponible |
| Suppression | Retourne dans "À créer" |
| Action | Bouton "Valider" |

**Colonne "Validées" :**
| Élément | Détail |
|---------|--------|
| Statut | Verrouillée, non modifiable |
| Retour brouillon | Possible TANT QUE non envoyée |
| Visualisation | PDF disponible |
| Envoi | Email + horodatage (date/heure) |
| Annulation | Non - Avoirs manuels (V2) |

---

### Écran 5b : Liste Factures

| Élément | Détail |
|---------|--------|
| **Colonnes** | Numéro, Client (lien), Date émission, Date échéance, Montant TTC, Statut |
| **Statuts** | Brouillon / Validée / Envoyée / Payée |
| **Recherche** | Barre de recherche rapide |
| **Filtres** | Période, Statut, Client |
| **Export** | CSV |
| **Liens** | Accès rapide fiche client + contrat |
| **Actions** | Voir PDF, Renvoyer email |

---

### Écran 6 : Paramètres

**6a. Informations Émetteur :**
| Champ |
|-------|
| Raison sociale |
| Forme juridique |
| Capital social |
| Adresse |
| SIREN |
| N° TVA |
| Email |
| Téléphone |
| IBAN / BIC |
| Logo |

**6b. Catalogue Modules :**
| Champ |
|-------|
| Nom du module |
| Prix unitaire par défaut |
| Taux TVA (20% par défaut) |
| Actif / Inactif |

**6c. Gestion CGV :**
| Élément |
|---------|
| Upload PDF |
| Versioning (date validité) |
| CGV par défaut |
| Historique versions |

**6d. Paramètres Facturation :**
| Paramètre |
|-----------|
| Format numérotation (à définir) |
| Délai échéance par défaut (30 jours) |
| Mentions légales |
| Email expéditeur |

**6e. Utilisateurs (préparation V2) :**
| Champ |
|-------|
| Nom |
| Email |
| Rôle (V2) |
| Actif / Inactif |

---

## Format Facture

**En-tête :**
| Élément |
|---------|
| Logo / Raison sociale émetteur |
| Numéro facture (format à définir) |
| Date d'émission |
| Date d'échéance |

**Émetteur :**
Raison sociale, Adresse, Email, Téléphone, SIREN, TVA

**Client :**
Raison sociale, SIREN, Adresse, N° TVA

**Lignes :**
| Colonne |
|---------|
| Module |
| Quantité (licences) |
| Prix unitaire HT |
| Remise ligne (%) - optionnel |
| TVA (%) |
| Total HT |

**Récapitulatif :**
- Total HT avant remise
- Remise globale (%) - optionnel
- Total HT
- Total TVA
- Total TTC

**Paiement :**
IBAN, BIC

**Mentions légales :**
Pénalités de retard, Indemnité 40 €, Infos société

---

## Hors scope V1 (reporté V2)

| Fonctionnalité |
|----------------|
| Suivi paiements / Rapprochement |
| Avoirs |
| Factures manuelles |
| Rôles différenciés / Restrictions données |
| Intégration comptabilité |
| API import licences |

---

## Prochaines étapes

1. ✅ **Brainstorming terminé**
2. 📋 **Product Brief** - Formaliser la vision
3. 📄 **PRD** - Spécifications détaillées
4. 🏗️ **Architecture** - Choix techniques
5. 📝 **Epics & Stories** - Découpage en tâches
