# Epics & Stories - Factu

**Version:** 1.0
**Date:** 2026-01-20
**Statut:** Validé

---

## Vue d'ensemble

### Récapitulatif des Epics

| # | Epic | Stories | Priorité | Dépendances |
|---|------|---------|----------|-------------|
| E0 | Setup & Infrastructure | 8 | Critique | - |
| E1 | Authentification | 5 | Critique | E0 |
| E2 | Paramètres | 10 | Haute | E1 |
| E3 | Gestion Clients | 12 | Haute | E2 |
| E4 | Gestion Contrats | 14 | Haute | E3 |
| E5 | Gestion Licences | 12 | Haute | E4 |
| E6 | Facturation | 16 | Haute | E5 |
| E7 | Dashboard | 5 | Moyenne | E6 |
| E8 | Finitions & Polish | 6 | Moyenne | E7 |
| **Total** | | **88** | | |

### Ordre de développement recommandé

```
E0 → E1 → E2 → E3 → E4 → E5 → E6 → E7 → E8
```

---

## Epic 0 : Setup & Infrastructure

**Objectif:** Mettre en place le projet Symfony et l'infrastructure de base.

### Stories

| ID | Story | Points | Priorité |
|----|-------|--------|----------|
| E0-S1 | Initialiser le projet Symfony 7 | 2 | Critique |
| E0-S2 | Configurer la base de données PostgreSQL | 2 | Critique |
| E0-S3 | Installer et configurer Webpack Encore + Tailwind | 3 | Critique |
| E0-S4 | Configurer Symfony UX (Turbo, Stimulus) | 2 | Critique |
| E0-S5 | Créer le layout de base (header, sidebar, footer) | 3 | Critique |
| E0-S6 | Créer les composants UI réutilisables (boutons, cards, alerts) | 3 | Haute |
| E0-S7 | Configurer les variables d'environnement | 1 | Critique |
| E0-S8 | Mettre en place la structure de dossiers | 1 | Critique |

**Total:** 17 points

### Détails

#### E0-S1 : Initialiser le projet Symfony 7
```
En tant que développeur
Je veux créer le projet Symfony de base
Afin d'avoir une fondation pour le développement

Critères d'acceptation:
- [ ] Projet créé avec symfony/skeleton
- [ ] Bundles de base installés (orm-pack, twig-pack, security-bundle, maker-bundle)
- [ ] Structure MVC en place
- [ ] Serveur de dev fonctionnel
```

#### E0-S5 : Créer le layout de base
```
En tant qu'utilisateur
Je veux avoir une interface cohérente
Afin de naviguer facilement dans l'application

Critères d'acceptation:
- [ ] Header avec logo et menu utilisateur
- [ ] Sidebar avec navigation principale
- [ ] Zone de contenu principal responsive
- [ ] Footer avec mentions
- [ ] Design Tailwind appliqué
```

---

## Epic 1 : Authentification

**Objectif:** Permettre aux utilisateurs de se connecter et gérer leurs sessions.

### Stories

| ID | Story | Points | Priorité |
|----|-------|--------|----------|
| E1-S1 | Créer l'entité User | 2 | Critique |
| E1-S2 | Implémenter la page de login | 3 | Critique |
| E1-S3 | Configurer la sécurité Symfony | 2 | Critique |
| E1-S4 | Implémenter la déconnexion | 1 | Critique |
| E1-S5 | Protéger les routes (accès authentifié) | 1 | Critique |

**Total:** 9 points

### Détails

#### E1-S1 : Créer l'entité User
```
En tant que développeur
Je veux créer l'entité User avec Doctrine
Afin de gérer les utilisateurs en base

Critères d'acceptation:
- [ ] Entité User avec champs: id, nom, email, password, roles, actif, createdAt, updatedAt
- [ ] Repository UserRepository
- [ ] Migration créée et appliquée
- [ ] Fixtures pour créer un admin par défaut
```

#### E1-S2 : Implémenter la page de login
```
En tant qu'utilisateur
Je veux pouvoir me connecter avec email/mot de passe
Afin d'accéder à l'application

Critères d'acceptation:
- [ ] Page de login avec formulaire (email, password)
- [ ] Validation des champs
- [ ] Message d'erreur si identifiants incorrects
- [ ] Redirection vers dashboard après connexion
- [ ] Remember me fonctionnel
```

---

## Epic 2 : Paramètres

**Objectif:** Configurer les données de base de l'application (émetteur, modules, CGV, facturation).

### Stories

| ID | Story | Points | Priorité |
|----|-------|--------|----------|
| E2-S1 | Créer l'entité Emetteur | 2 | Haute |
| E2-S2 | Implémenter l'écran de paramétrage émetteur | 3 | Haute |
| E2-S3 | Créer l'entité Module | 2 | Haute |
| E2-S4 | Implémenter le CRUD des modules | 3 | Haute |
| E2-S5 | Créer l'entité Cgv | 2 | Haute |
| E2-S6 | Implémenter la gestion des CGV (upload, versioning) | 3 | Haute |
| E2-S7 | Créer l'entité ParametreFacturation | 2 | Haute |
| E2-S8 | Implémenter l'écran paramètres facturation | 2 | Haute |
| E2-S9 | Implémenter la gestion des utilisateurs (CRUD) | 3 | Moyenne |
| E2-S10 | Upload du logo émetteur | 2 | Moyenne |

**Total:** 24 points

### Détails

#### E2-S2 : Implémenter l'écran de paramétrage émetteur
```
En tant qu'administrateur
Je veux configurer les informations de l'émetteur
Afin qu'elles apparaissent sur les factures

Critères d'acceptation:
- [ ] Formulaire avec tous les champs (raison sociale, forme juridique, capital, adresse, SIREN, TVA, email, téléphone, IBAN, BIC)
- [ ] Validation des champs
- [ ] Sauvegarde en base
- [ ] Message de confirmation après sauvegarde
```

#### E2-S4 : Implémenter le CRUD des modules
```
En tant qu'administrateur
Je veux gérer le catalogue des modules
Afin de définir les produits vendus

Critères d'acceptation:
- [ ] Liste des modules avec colonnes: nom, prix défaut, TVA, statut
- [ ] Formulaire création/modification
- [ ] Possibilité de désactiver un module (pas de suppression)
- [ ] Message de confirmation
```

---

## Epic 3 : Gestion Clients

**Objectif:** Gérer la base clients (CRUD, contacts, notes, liens).

### Stories

| ID | Story | Points | Priorité |
|----|-------|--------|----------|
| E3-S1 | Créer les entités Client, Contact, ClientNote, ClientLien | 3 | Haute |
| E3-S2 | Implémenter la liste des clients | 3 | Haute |
| E3-S3 | Implémenter la recherche clients | 2 | Haute |
| E3-S4 | Implémenter le filtre clients inactifs | 1 | Haute |
| E3-S5 | Implémenter l'export CSV clients | 2 | Haute |
| E3-S6 | Implémenter la fiche client 360° | 5 | Haute |
| E3-S7 | Implémenter le formulaire création/modification client | 3 | Haute |
| E3-S8 | Implémenter la validation SIREN | 2 | Haute |
| E3-S9 | Implémenter la validation IBAN | 2 | Haute |
| E3-S10 | Implémenter la gestion des contacts | 3 | Haute |
| E3-S11 | Implémenter les notes horodatées | 2 | Moyenne |
| E3-S12 | Implémenter les liens entre clients | 2 | Moyenne |

**Total:** 30 points

### Détails

#### E3-S2 : Implémenter la liste des clients
```
En tant qu'utilisateur
Je veux voir la liste de tous les clients
Afin d'avoir une vue d'ensemble

Critères d'acceptation:
- [ ] Tableau avec colonnes: nom (lien), SIREN, nb licences, statut
- [ ] Pagination (20 par page)
- [ ] Tri sur chaque colonne
- [ ] Clic sur nom → fiche client
- [ ] Bouton "Nouveau client"
```

#### E3-S6 : Implémenter la fiche client 360°
```
En tant qu'utilisateur
Je veux voir toutes les informations d'un client
Afin de le gérer efficacement

Critères d'acceptation:
- [ ] Section informations générales
- [ ] Section contacts avec liste
- [ ] Section contrats avec liste
- [ ] Lien vers factures
- [ ] Section liens clients
- [ ] Section notes avec historique
- [ ] Boutons modifier/supprimer
```

#### E3-S8 : Implémenter la validation SIREN
```
En tant qu'utilisateur
Je veux que le SIREN soit validé à la saisie
Afin d'éviter les erreurs

Critères d'acceptation:
- [ ] Validation format (9 chiffres)
- [ ] Validation algorithme de Luhn
- [ ] Message d'erreur explicite si invalide
- [ ] Validation en temps réel (live component ou JS)
```

---

## Epic 4 : Gestion Contrats

**Objectif:** Gérer les contrats (CRUD, lignes, événements, fichiers, CGV).

### Stories

| ID | Story | Points | Priorité |
|----|-------|--------|----------|
| E4-S1 | Créer les entités Contrat, LigneContrat, ContratEvenement, ContratFichier, ContratCgv | 3 | Haute |
| E4-S2 | Créer l'entité Instance et InstanceNom | 2 | Haute |
| E4-S3 | Implémenter la liste des contrats | 3 | Haute |
| E4-S4 | Implémenter la recherche/filtres contrats | 2 | Haute |
| E4-S5 | Implémenter l'export CSV contrats | 2 | Haute |
| E4-S6 | Implémenter la fiche contrat | 5 | Haute |
| E4-S7 | Implémenter le formulaire création/modification contrat | 3 | Haute |
| E4-S8 | Implémenter la gestion des lignes tarifaires | 3 | Haute |
| E4-S9 | Implémenter la gestion des événements | 2 | Haute |
| E4-S10 | Implémenter l'upload de fichiers PDF | 3 | Haute |
| E4-S11 | Implémenter l'association CGV au contrat | 2 | Moyenne |
| E4-S12 | Implémenter le graphique évolution licences (12 mois) | 3 | Moyenne |
| E4-S13 | Créer l'entité HistoriqueLicence | 2 | Haute |
| E4-S14 | Afficher les relevés en attente sur fiche contrat | 2 | Haute |

**Total:** 37 points

### Détails

#### E4-S6 : Implémenter la fiche contrat
```
En tant qu'utilisateur
Je veux voir toutes les informations d'un contrat
Afin de le gérer efficacement

Critères d'acceptation:
- [ ] Infos générales (client, instance, dates, périodicité, statut)
- [ ] Flag "facture particulière"
- [ ] Tableau lignes tarifaires avec total
- [ ] Section événements
- [ ] Section fichiers avec upload
- [ ] Section CGV
- [ ] Graphique évolution licences
- [ ] Relevés en attente
```

#### E4-S8 : Implémenter la gestion des lignes tarifaires
```
En tant qu'utilisateur
Je veux gérer les lignes tarifaires d'un contrat
Afin de définir ce qui sera facturé

Critères d'acceptation:
- [ ] Tableau avec colonnes: module, quantité, prix unitaire, remise, TVA, total HT
- [ ] Ajout de ligne (sélection module)
- [ ] Modification en ligne ou modal
- [ ] Suppression avec confirmation
- [ ] Calcul automatique des totaux
```

---

## Epic 5 : Gestion Licences

**Objectif:** Importer les relevés de licences, gérer le mapping instances, traiter les relevés.

### Stories

| ID | Story | Points | Priorité |
|----|-------|--------|----------|
| E5-S1 | Créer les entités ImportLicence, ReleveLicence | 2 | Haute |
| E5-S2 | Créer l'entité InstanceClient | 2 | Haute |
| E5-S3 | Implémenter l'écran d'import CSV | 3 | Haute |
| E5-S4 | Implémenter le parsing et validation du CSV | 3 | Haute |
| E5-S5 | Implémenter la prévisualisation avant import | 3 | Haute |
| E5-S6 | Implémenter l'archivage du fichier CSV source | 2 | Haute |
| E5-S7 | Implémenter l'écran de traitement des relevés | 4 | Haute |
| E5-S8 | Implémenter l'application d'un relevé au contrat | 3 | Haute |
| E5-S9 | Implémenter l'application en masse des relevés | 2 | Moyenne |
| E5-S10 | Implémenter l'écran de mapping instances | 4 | Haute |
| E5-S11 | Implémenter le mapping multi-clients (pourcentages) | 3 | Moyenne |
| E5-S12 | Implémenter l'historique des noms d'instance | 2 | Moyenne |

**Total:** 33 points

### Détails

#### E5-S3 : Implémenter l'écran d'import CSV
```
En tant qu'utilisateur
Je veux importer un fichier CSV de relevés de licences
Afin de mettre à jour les données

Critères d'acceptation:
- [ ] Zone de drag & drop ou bouton parcourir
- [ ] Indication du format attendu
- [ ] Contrôle du type de fichier (CSV uniquement)
- [ ] Feedback visuel pendant l'upload
```

#### E5-S7 : Implémenter l'écran de traitement des relevés
```
En tant qu'utilisateur
Je veux voir tous les relevés en attente de traitement
Afin de les appliquer aux contrats

Critères d'acceptation:
- [ ] Tableau avec colonnes: instance, client, date, licences, delta, statut
- [ ] Checkbox pour sélection multiple
- [ ] Filtres (période, statut)
- [ ] Alerte visuelle si écart > 10%
- [ ] Boutons: Appliquer, Ignorer
- [ ] Bouton "Appliquer sélection"
```

---

## Epic 6 : Facturation

**Objectif:** Générer, valider et envoyer les factures.

### Stories

| ID | Story | Points | Priorité |
|----|-------|--------|----------|
| E6-S1 | Créer les entités Facture, LigneFacture, FactureEnvoi | 3 | Haute |
| E6-S2 | Implémenter l'écran workflow facturation (3 colonnes) | 5 | Haute |
| E6-S3 | Implémenter le calcul des factures à créer | 4 | Haute |
| E6-S4 | Implémenter la détection des alertes (écart licences, facture particulière) | 2 | Haute |
| E6-S5 | Implémenter la création d'un brouillon | 3 | Haute |
| E6-S6 | Implémenter la modification d'un brouillon | 2 | Haute |
| E6-S7 | Implémenter la suppression d'un brouillon (retour "à créer") | 1 | Haute |
| E6-S8 | Implémenter la validation d'une facture (numéro définitif) | 3 | Haute |
| E6-S9 | Implémenter le retour en brouillon (si non envoyée) | 2 | Haute |
| E6-S10 | Créer le template PDF de facture | 5 | Haute |
| E6-S11 | Implémenter la génération PDF (DomPDF) | 3 | Haute |
| E6-S12 | Implémenter la prévisualisation PDF | 2 | Haute |
| E6-S13 | Implémenter l'envoi de facture par email | 3 | Haute |
| E6-S14 | Implémenter l'horodatage des envois | 1 | Haute |
| E6-S15 | Implémenter la liste des factures | 3 | Haute |
| E6-S16 | Implémenter l'export CSV factures | 2 | Haute |

**Total:** 44 points

### Détails

#### E6-S2 : Implémenter l'écran workflow facturation
```
En tant qu'utilisateur
Je veux voir les factures dans un workflow visuel
Afin de suivre leur progression

Critères d'acceptation:
- [ ] 3 colonnes: À créer, Brouillons, Validées
- [ ] Cards avec résumé de chaque facture
- [ ] Alertes visuelles (badges)
- [ ] Actions par facture (boutons)
- [ ] Bouton "Calculer les factures"
- [ ] Compteurs par colonne
```

#### E6-S10 : Créer le template PDF de facture
```
En tant qu'utilisateur
Je veux que les factures PDF soient professionnelles
Afin de les envoyer aux clients

Critères d'acceptation:
- [ ] En-tête avec logo et infos émetteur
- [ ] Infos client
- [ ] Numéro, dates émission/échéance
- [ ] Tableau des lignes (module, qté, prix, remise, TVA, total)
- [ ] Récapitulatif TVA
- [ ] Total TTC
- [ ] Infos paiement (IBAN/BIC)
- [ ] Mentions légales
- [ ] Pied de page avec infos société
```

#### E6-S13 : Implémenter l'envoi de facture par email
```
En tant qu'utilisateur
Je veux envoyer une facture par email
Afin de la transmettre au client

Critères d'acceptation:
- [ ] Bouton "Envoyer" sur facture validée
- [ ] Modal de confirmation avec adresse email
- [ ] Envoi avec PDF en pièce jointe
- [ ] Template email configurable
- [ ] Enregistrement de l'envoi avec date/heure
- [ ] Mise à jour statut facture → "Envoyée"
```

---

## Epic 7 : Dashboard

**Objectif:** Afficher les indicateurs clés sur la page d'accueil.

### Stories

| ID | Story | Points | Priorité |
|----|-------|--------|----------|
| E7-S1 | Créer le service de calcul des statistiques | 3 | Moyenne |
| E7-S2 | Implémenter l'affichage du nombre de clients actifs | 1 | Moyenne |
| E7-S3 | Implémenter l'affichage CA mois précédent/en cours | 2 | Moyenne |
| E7-S4 | Implémenter l'affichage CA cumulé année en cours/passée | 2 | Moyenne |
| E7-S5 | Implémenter l'affichage montant en attente de paiement | 2 | Moyenne |

**Total:** 10 points

### Détails

#### E7-S1 : Créer le service de calcul des statistiques
```
En tant que développeur
Je veux un service centralisant les calculs de stats
Afin de les réutiliser facilement

Critères d'acceptation:
- [ ] Méthode: getClientsActifs()
- [ ] Méthode: getCaMois($mois, $annee)
- [ ] Méthode: getNbFacturesMois($mois, $annee)
- [ ] Méthode: getCaCumuleAnnee($annee)
- [ ] Méthode: getMontantEnAttente()
- [ ] Optimisation des requêtes SQL
```

---

## Epic 8 : Finitions & Polish

**Objectif:** Finaliser l'application avec les derniers ajustements.

### Stories

| ID | Story | Points | Priorité |
|----|-------|--------|----------|
| E8-S1 | Implémenter les messages flash (succès, erreur, warning) | 2 | Moyenne |
| E8-S2 | Implémenter la pagination cohérente sur toutes les listes | 2 | Moyenne |
| E8-S3 | Ajouter les confirmations avant suppression | 1 | Moyenne |
| E8-S4 | Optimiser les performances (requêtes N+1, index DB) | 3 | Moyenne |
| E8-S5 | Tester et corriger les cas limites | 3 | Moyenne |
| E8-S6 | Rédiger la documentation utilisateur | 3 | Basse |

**Total:** 14 points

---

## Récapitulatif par priorité

### Critique (à faire en premier)
| Epic | Stories | Points |
|------|---------|--------|
| E0 - Setup | 8 | 17 |
| E1 - Auth | 5 | 9 |
| **Total** | **13** | **26** |

### Haute (cœur fonctionnel)
| Epic | Stories | Points |
|------|---------|--------|
| E2 - Paramètres | 8 | 19 |
| E3 - Clients | 10 | 26 |
| E4 - Contrats | 12 | 32 |
| E5 - Licences | 9 | 27 |
| E6 - Facturation | 16 | 44 |
| **Total** | **55** | **148** |

### Moyenne / Basse (finitions)
| Epic | Stories | Points |
|------|---------|--------|
| E2 - Paramètres (suite) | 2 | 5 |
| E3 - Clients (suite) | 2 | 4 |
| E4 - Contrats (suite) | 2 | 5 |
| E5 - Licences (suite) | 3 | 6 |
| E7 - Dashboard | 5 | 10 |
| E8 - Finitions | 6 | 14 |
| **Total** | **20** | **44** |

### Total global
| | Stories | Points |
|--|---------|--------|
| **TOTAL** | **88** | **218** |

---

## Planning suggéré

### Sprint 1 : Fondations
| Epic | Stories |
|------|---------|
| E0 | S1 à S8 |
| E1 | S1 à S5 |

### Sprint 2 : Paramètres & Clients
| Epic | Stories |
|------|---------|
| E2 | S1 à S8 |
| E3 | S1 à S5 |

### Sprint 3 : Clients (suite) & Contrats
| Epic | Stories |
|------|---------|
| E3 | S6 à S12 |
| E4 | S1 à S7 |

### Sprint 4 : Contrats (suite) & Licences
| Epic | Stories |
|------|---------|
| E4 | S8 à S14 |
| E5 | S1 à S6 |

### Sprint 5 : Licences (suite) & Facturation
| Epic | Stories |
|------|---------|
| E5 | S7 à S12 |
| E6 | S1 à S7 |

### Sprint 6 : Facturation (suite)
| Epic | Stories |
|------|---------|
| E6 | S8 à S16 |

### Sprint 7 : Dashboard & Finitions
| Epic | Stories |
|------|---------|
| E7 | S1 à S5 |
| E8 | S1 à S6 |
| E2 | S9 à S10 |

---

## Annexes

### Estimation des points

| Points | Complexité | Durée estimée |
|--------|------------|---------------|
| 1 | Trivial | ~2h |
| 2 | Simple | ~4h |
| 3 | Moyenne | ~1 jour |
| 4 | Complexe | ~1.5 jours |
| 5 | Très complexe | ~2 jours |

### Documents liés
- [Product Brief](./product-brief.md)
- [PRD](./prd.md)
- [Architecture](./architecture.md)
- [Brainstorming](../analysis/brainstorming-session-2026-01-16.md)
