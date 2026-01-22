# Product Brief - Factu

**Version:** 1.0
**Date:** 2026-01-20
**Statut:** Validé

---

## 1. Vision Produit

### Énoncé de vision
**Factu** est une application de facturation interne permettant de gérer efficacement la vente d'abonnements logiciels multi-modules, avec une traçabilité complète des licences pour éliminer les litiges clients.

### Problème à résoudre
Aujourd'hui, la gestion de la facturation repose sur des outils dispersés (Pennylane pour la facturation, fichiers manuels pour le suivi des licences), ce qui génère :
- Des **contestations clients** sur le nombre de licences facturées (absence de preuve)
- Un processus **chronophage** de mise à jour manuelle des licences
- Une **méconnaissance des CGV** par les clients
- L'absence d'une **source de vérité unique** pour les données clients

### Solution proposée
Une application centralisée qui :
- Gère la base clients comme **source de vérité**
- **Historise** tous les relevés de licences avec archivage des fichiers sources
- Automatise la **génération des factures** selon les périodicités contractuelles
- Fournit une **traçabilité complète** pour justifier chaque facturation

---

## 2. Objectifs

### Objectifs business
| Objectif | Mesure de succès |
|----------|------------------|
| Réduire les litiges clients | 0 contestation non résolue par manque de preuve |
| Accélérer le processus de facturation | Temps de génération des factures mensuelles divisé par 2 |
| Centraliser les données clients | 100% des clients dans l'outil |

### Objectifs utilisateurs
| Objectif | Mesure de succès |
|----------|------------------|
| Simplifier l'import des licences | Import en moins de 5 minutes |
| Visualiser rapidement l'état d'un client | Fiche 360° accessible en 1 clic |
| Générer les factures sans ressaisie | 0 saisie manuelle des montants |

---

## 3. Utilisateurs cibles

### Personas

**Persona 1 : Comptable**
| Attribut | Détail |
|----------|--------|
| Rôle | Responsable facturation |
| Objectifs | Émettre les factures mensuelles, suivre les paiements |
| Frustrations | Ressaisie manuelle, contestations clients, recherche d'informations dispersées |
| Usage | Quotidien |

**Persona 2 : Direction**
| Attribut | Détail |
|----------|--------|
| Rôle | Supervision, décision |
| Objectifs | Suivre le CA, valider les contrats, avoir une vue d'ensemble |
| Frustrations | Manque de visibilité sur les chiffres clés |
| Usage | Hebdomadaire |

### Volumétrie estimée
| Élément | Volume |
|---------|--------|
| Utilisateurs | 4-5 |
| Clients | ~400 |
| Factures / mois | ~300 |

---

## 4. Périmètre fonctionnel

### V1 - MVP

#### Gestion des données
| Fonctionnalité | Priorité |
|----------------|----------|
| CRUD Clients (avec validation SIREN, IBAN) | Haute |
| CRUD Contrats (lignes tarifaires, CGV) | Haute |
| Gestion des contacts multiples | Haute |
| Historisation des modifications | Haute |
| Liens entre clients (fusion/scission) | Moyenne |

#### Gestion des licences
| Fonctionnalité | Priorité |
|----------------|----------|
| Import CSV des relevés | Haute |
| Archivage fichiers sources | Haute |
| Mapping instance → client(s) | Haute |
| Historique des noms d'instance | Moyenne |
| Alertes écarts licences | Moyenne |

#### Facturation
| Fonctionnalité | Priorité |
|----------------|----------|
| Calcul automatique des factures à émettre | Haute |
| Workflow 3 colonnes (À créer / Brouillon / Validée) | Haute |
| Génération PDF | Haute |
| Envoi email avec horodatage | Haute |
| Alertes factures particulières | Moyenne |

#### Paramétrage
| Fonctionnalité | Priorité |
|----------------|----------|
| Informations émetteur | Haute |
| Catalogue modules (prix par défaut) | Haute |
| Gestion CGV versionnées | Haute |
| Paramètres facturation | Haute |

#### Reporting
| Fonctionnalité | Priorité |
|----------------|----------|
| Dashboard chiffres clés | Haute |
| Exports CSV (clients, contrats, factures) | Haute |

### V2 - Évolutions futures
| Fonctionnalité |
|----------------|
| Suivi paiements / Rapprochement bancaire |
| Avoirs |
| Factures manuelles |
| Rôles et permissions utilisateurs |
| Intégration comptabilité |
| API import licences |

### Hors périmètre
| Élément |
|---------|
| Gestion des devis |
| Prorata temporis |
| Multi-devises |
| Multi-sociétés |

---

## 5. Règles métier clés

| Règle | Description |
|-------|-------------|
| Pas de prorata | Facturation mois complet uniquement |
| Résiliation | Uniquement à date anniversaire |
| Modification tarif | Historisée avec date de prise en compte |
| Numéro de facture | Séquentiel, format à définir |
| Annulation facture | Impossible - création d'avoir |
| Facture validée | Non modifiable, retour brouillon possible si non envoyée |

---

## 6. Contraintes

### Contraintes techniques
| Contrainte | Impact |
|------------|--------|
| Application web | Accessible depuis navigateur |
| Stockage fichiers | PDF (CGV, contrats, factures), CSV (imports) |
| Envoi emails | SMTP ou service tiers |

### Contraintes réglementaires
| Contrainte | Impact |
|------------|--------|
| Mentions légales factures | Obligatoires (pénalités retard, indemnité 40€) |
| Conservation factures | Archivage légal |
| Validation SIREN/TVA | Contrôle de cohérence |

### Contraintes organisationnelles
| Contrainte | Impact |
|------------|--------|
| 4-5 utilisateurs | Pas besoin de gestion de charge importante |
| Utilisateurs internes | Pas d'accès client externe |

---

## 7. Critères de succès

### Critères d'acceptation V1
| Critère | Validation |
|---------|------------|
| Import d'un fichier CSV de licences | Fonctionnel |
| Création d'un client avec validation SIREN/IBAN | Fonctionnel |
| Création d'un contrat avec lignes tarifaires | Fonctionnel |
| Génération d'une facture PDF | Fonctionnel |
| Envoi d'une facture par email | Fonctionnel |
| Affichage du dashboard chiffres clés | Fonctionnel |
| Export CSV des listes | Fonctionnel |

### KPIs post-lancement
| KPI | Cible |
|-----|-------|
| Temps moyen génération factures mensuelles | < 30 min |
| Contestations clients liées aux licences | 0 |
| Taux d'adoption | 100% utilisateurs cibles |

---

## 8. Risques identifiés

| Risque | Probabilité | Impact | Mitigation |
|--------|-------------|--------|------------|
| Résistance au changement | Moyenne | Moyen | Formation, accompagnement |
| Données initiales incomplètes | Haute | Moyen | Import initial accompagné |
| Format CSV non standard | Moyenne | Faible | Documentation format attendu |

---

## 9. Planning indicatif

| Phase | Description |
|-------|-------------|
| Phase 1 | PRD - Spécifications détaillées |
| Phase 2 | Architecture technique |
| Phase 3 | Développement V1 |
| Phase 4 | Tests et recette |
| Phase 5 | Déploiement et formation |

---

## 10. Annexes

### Documents liés
- [Brainstorming Session](../analysis/brainstorming-session-2026-01-16.md)
- PRD (à venir)
- Architecture (à venir)

### Exemple de facture actuelle
Format Pennylane avec :
- En-tête émetteur/client
- Lignes par module (quantité, prix unitaire, remise, TVA)
- Récapitulatif TVA
- Informations de paiement (IBAN/BIC)
- Mentions légales
