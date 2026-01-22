# Journal des erreurs et solutions - Factu

Ce document recense les erreurs rencontrees sur le projet et les solutions appliquees.

---

## ERR-001 : Duplicate entry pour numero de facture

**Date** : 2026-01-22

**Message d'erreur** :
```
SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'FAC-2026-00001' for key 'facture.UNIQ_FE866410F55AE19E'
```

**Cause** :
Les fixtures creaient des factures avec des numeros (FAC-2026-00001, 00002, 00003) mais le compteur `prochainNumero` dans `ParametreFacturation` restait a 1.

**Solution** :
Modifier `AppFixtures.php` pour initialiser le compteur a 4 :
```php
$params->setProchainNumero(4); // 3 factures creees dans les fixtures
```

**Fichiers modifies** :
- `src/DataFixtures/AppFixtures.php`

---

## ERR-002 : Token CSRF invalide

**Date** : 2026-01-22

**Message d'erreur** :
```
Invalid CSRF token.
```
ou
```
Token CSRF invalide
```

**Cause** :
Utilisation de `$request->getPayload()->getString('_token')` pour recuperer le token CSRF. Cette methode est destinee aux requetes JSON/raw body, pas aux formulaires POST classiques.

**Solution** :
Remplacer par `$request->request->get('_token')` pour les formulaires POST :
```php
// Avant (incorrect pour les formulaires)
$request->getPayload()->getString('_token')

// Apres (correct)
$request->request->get('_token')
```

**Fichiers modifies** :
- `src/Controller/FacturationController.php`
- `src/Controller/ContratController.php`

---

## ERR-003 : Contrats visibles avant leur date de debut de facturation

**Date** : 2026-01-22

**Message d'erreur** :
Aucun message d'erreur, mais comportement incorrect : des contrats apparaissaient dans le workflow de facturation pour des mois anterieurs a leur date de debut.

**Cause** :
Le champ `dateDebutFacturation` n'existait pas. Le systeme utilisait uniquement `dateAnniversaire` pour determiner quand facturer, sans verifier si le contrat etait actif a cette periode.

**Solution** :
1. Ajouter le champ `dateDebutFacturation` a l'entite `Contrat`
2. Mettre a jour `ContratRepository::findAFacturer()` pour filtrer avec `dateDebutFacturation <= :date`
3. Mettre a jour `FacturationService::doitEtreFacture()` pour verifier que la periode est apres `dateDebutFacturation`

**Fichiers modifies** :
- `src/Entity/Contrat.php`
- `src/Repository/ContratRepository.php`
- `src/Service/FacturationService.php`
- `src/Form/ContratType.php`
- `src/DataFixtures/AppFixtures.php`
- `migrations/Version20260122170855.php`

---

## ERR-004 : Invalid CSRF token sur la page de connexion

**Date** : 2026-01-22

**Message d'erreur** :
```
Invalid CSRF token.
```

**Cause** :
Session perimee apres rechargement des fixtures ou expiration de session. Le token CSRF stocke dans le formulaire ne correspond plus a celui de la session serveur.

**Solution** :
1. Rafraichir la page (F5)
2. Si persistant, vider les cookies du navigateur pour le site

**Fichiers concernes** :
- Aucune modification necessaire (comportement normal de securite)

---

## ERR-005 : Contrats trimestriels/annuels visibles dans les mauvais mois

**Date** : 2026-01-22

**Message d'erreur** :
Aucun message, mais comportement incorrect : les contrats trimestriels et annuels apparaissaient dans le workflow pour tous les mois au lieu d'etre limites aux mois concernes.

**Cause** :
1. La date de reference utilisait le jour 15 du mois, ce qui pouvait manquer certains contrats avec jour anniversaire > 15
2. La navigation entre mois avec `+1 month` sur le dernier jour d'un mois (ex: 31 janvier) sautait des mois (janvier 31 + 1 mois = mars 3)

**Solution** :
1. Utiliser le dernier jour du mois comme date de reference pour le filtrage des contrats
2. Utiliser le 1er du mois pour calculer la navigation precedent/suivant

```php
// Date de reference = dernier jour du mois
$dateReference = new \DateTime($moisParam . '-01');
$dateReference->modify('last day of this month');

// Navigation = basee sur le 1er du mois
$premierDuMois = new \DateTime($dateReference->format('Y-m-01'));
$moisPrecedent = (clone $premierDuMois)->modify('-1 month')->format('Y-m');
$moisSuivant = (clone $premierDuMois)->modify('+1 month')->format('Y-m');
```

**Fichiers modifies** :
- `src/Controller/FacturationController.php`

**Tests ajoutes** :
- `testAnnualContractsOnlyShowInAnniversaryMonth`
- `testQuarterlyContractsOnlyShowInFirstMonthOfQuarter`

---

## Template pour nouvelles erreurs

```markdown
## ERR-XXX : [Titre court]

**Date** : YYYY-MM-DD

**Message d'erreur** :
```
[Message exact]
```

**Cause** :
[Description de la cause racine]

**Solution** :
[Description de la solution avec code si necessaire]

**Fichiers modifies** :
- [Liste des fichiers]
```
