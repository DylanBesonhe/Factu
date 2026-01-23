# Specification : Factures ponctuelles et Avoirs

**Version:** 1.0
**Date:** 2026-01-23
**Statut:** A valider

---

## 1. Resume executif

Cette specification decrit l'ajout de deux fonctionnalites majeures :
1. **Factures ponctuelles** : Factures creees manuellement, optionnellement liees a un contrat
2. **Avoirs** : Documents d'annulation totale ou partielle de factures

---

## 2. Modifications du modele de donnees

### 2.1 Entite Facture - Nouveaux champs

| Champ | Type | Nullable | Description |
|-------|------|----------|-------------|
| `type` | string(20) | Non | `facture` ou `avoir` |
| `contrat` | ManyToOne | **Oui** | Devient nullable pour les ponctuelles |
| `factureParente` | ManyToOne(self) | Oui | Reference a la facture annulee (avoirs lies) |
| `avoirs` | OneToMany(self) | - | Collection des avoirs lies a cette facture |

### 2.2 Nouveaux statuts

```php
public const STATUT_BROUILLON = 'brouillon';
public const STATUT_VALIDEE = 'validee';
public const STATUT_ENVOYEE = 'envoyee';
public const STATUT_PAYEE = 'payee';
public const STATUT_ANNULEE = 'annulee';      // Nouveau
public const STATUT_REMBOURSEE = 'remboursee'; // Nouveau (pour avoirs lies)
```

### 2.3 Champs periode

Les champs `periodeDebut` et `periodeFin` deviennent :
- **Factures recurrentes** : Calcules automatiquement selon la periodicite
- **Factures ponctuelles** : `periodeDebut = periodeFin = dateFacture`
- **Avoirs** : `periodeDebut = periodeFin = dateFacture`

### 2.4 Numerotation

**Sequence unique partagee** entre factures et avoirs :
- Factures : `FAC-2026-0001`, `FAC-2026-0002`
- Avoirs : `AV-2026-0003`
- Factures : `FAC-2026-0004`

La sequence est continue sans trou, quel que soit le type de document.

**Modification de `ParametreFacturation`** :
- Un seul compteur `prochainNumero` pour les deux types
- Methode `genererNumero(string $type)` qui retourne `FAC-YYYY-NNNN` ou `AV-YYYY-NNNN`

---

## 3. Factures ponctuelles

### 3.1 Definition

Une facture ponctuelle est une facture :
- Creee manuellement par l'utilisateur
- **Optionnellement** liee a un contrat existant
- Sans notion de recurrence

### 3.2 Workflow de creation

```
[Menu Facturation > Factures manuelles]
              |
              v
    +-------------------+
    | Nouvelle facture  |
    +-------------------+
              |
              v
    +-------------------+
    | Selection client  |  (autocomplete)
    | Contrat (optionnel)|  (si selectionne, pre-remplit emetteur)
    | Emetteur          |  (par defaut si pas de contrat)
    | Date facture      |  (aujourd'hui par defaut)
    | Date echeance     |  (calculee selon parametres)
    +-------------------+
              |
              v
    +-------------------+
    | Ajout lignes      |
    | - Module catalogue|
    | - ou ligne libre  |
    +-------------------+
              |
              v
       [Brouillon cree]
              |
              v
    (meme workflow que factures recurrentes)
```

### 3.3 Regles metier

1. **Client obligatoire** : Une facture ponctuelle doit avoir un client
2. **Emetteur** :
   - Si contrat selectionne -> emetteur du contrat
   - Sinon -> emetteur par defaut (flag `parDefaut = true`)
3. **Lignes** :
   - Peuvent etre selectionnees depuis le catalogue de modules
   - Ou saisies librement (designation + prix)
4. **Periode** : `periodeDebut = periodeFin = dateFacture`
5. **Snapshot** : Les donnees client/emetteur sont snapshotees comme pour les recurrentes

---

## 4. Avoirs

### 4.1 Definition

Un avoir est un document qui :
- Annule totalement ou partiellement une facture
- Peut etre **lie** a une facture parente (recommande)
- Peut etre **libre** (sans lien, pour cas speciaux)
- A des montants positifs avec mention "A deduire"

### 4.2 Types d'avoirs

| Type | Description | Facture parente |
|------|-------------|-----------------|
| **Avoir total lie** | Annule 100% de la facture | Obligatoire |
| **Avoir partiel lie** | Annule une partie de la facture | Obligatoire |
| **Avoir libre** | Avoir sans lien (cas speciaux) | Aucune |

### 4.3 Workflow de creation - Avoir lie

```
[Facture validee/envoyee/payee]
              |
    [Bouton "Creer un avoir"]
              |
              v
    +-------------------+
    | Type d'avoir      |
    | ( ) Total         |
    | ( ) Partiel       |
    +-------------------+
              |
    +---------+---------+
    |                   |
    v                   v
[Total]            [Partiel]
Copie toutes       Selection lignes
les lignes         + quantites
    |                   |
    +---------+---------+
              |
              v
    +-------------------+
    | Motif de l'avoir  |  (champ texte)
    +-------------------+
              |
              v
       [Avoir brouillon]
              |
              v
    [Validation -> AV-2026-XXXX]
              |
              v
    [Impact sur facture parente]
```

### 4.4 Workflow de creation - Avoir libre

```
[Menu Facturation > Avoirs]
              |
    [Bouton "Nouvel avoir libre"]
              |
              v
    +-------------------+
    | Selection client  |
    | Emetteur          |
    | Date avoir        |
    | Motif             |
    +-------------------+
              |
              v
    +-------------------+
    | Ajout lignes      |
    | (comme facture)   |
    +-------------------+
              |
              v
       [Avoir brouillon]
```

### 4.5 Workflow des avoirs lies

```
+-------------+     +-------------+     +-------------+
|  BROUILLON  | --> |   VALIDEE   | --> | REMBOURSEE  |
+-------------+     +-------------+     +-------------+
                          |
                          v
                    [Impact sur facture parente]
```

- **Brouillon** : Modifiable, supprimable
- **Valide** : Numero attribue (AV-YYYY-NNNN), fige
- **Rembourse** : Le remboursement a ete effectue

### 4.6 Workflow des avoirs libres

Meme workflow que les factures classiques :
```
BROUILLON -> VALIDEE -> ENVOYEE -> PAYEE
```

### 4.7 Impact sur la facture parente

**Apres validation d'un avoir lie :**

| Situation | Action sur facture parente |
|-----------|---------------------------|
| Avoir total (100%) | Statut -> `annulee` |
| Avoir partiel | Pas de changement de statut |
| Somme avoirs >= total facture | Statut -> `annulee` |

**Calcul du montant restant :**
```
Montant restant = Facture.totalTtc - SUM(Avoirs lies.totalTtc)
```

### 4.8 Regles metier - Avoirs

1. **Avoirs multiples** : Une facture peut avoir plusieurs avoirs partiels
2. **Limite** : La somme des avoirs ne peut pas depasser le montant de la facture parente
3. **Statuts autorises** : Avoir creeable uniquement sur facture `validee`, `envoyee` ou `payee`
4. **Pas d'avoir sur avoir** : Un avoir ne peut pas avoir d'avoir
5. **Montants** : Toujours positifs dans la base, affiches avec mention "A deduire"

---

## 5. Interface utilisateur

### 5.1 Structure du menu

```
Facturation (accordeon)
├── Workflow recurrent     <- Page actuelle /facturation
├── Factures manuelles     <- Nouvelle page /facturation/manuelles
├── Avoirs                 <- Nouvelle page /facturation/avoirs
└── Historique             <- Liste complete /facturation/liste
```

### 5.2 Page "Factures manuelles"

**URL** : `/facturation/manuelles`

**Contenu** :
- Liste des factures ponctuelles (sans contrat ou avec contrat mais creees manuellement)
- Bouton "Nouvelle facture"
- Filtres : client, statut, periode
- Actions : voir, PDF, (modifier si brouillon)

### 5.3 Page "Avoirs"

**URL** : `/facturation/avoirs`

**Contenu** :
- Liste de tous les avoirs
- Bouton "Nouvel avoir libre"
- Colonnes : numero, date, client, facture parente (si lie), montant, statut
- Filtres : client, statut, lie/libre

### 5.4 Page detail facture - Modifications

**Ajouts** :
- Bouton "Creer un avoir" (si statut >= validee et type = facture)
- Section "Avoirs lies" listant les avoirs associes
- Badge "Annulee" si statut = annulee
- Montant restant du (si avoirs partiels)

### 5.5 Formulaire creation facture ponctuelle

**Champs** :
- Client (autocomplete, obligatoire)
- Contrat (select optionnel, filtre par client)
- Date facture (date, defaut aujourd'hui)
- Date echeance (date, calculee)
- Commentaire (textarea)
- Lignes (collection dynamique)

### 5.6 Formulaire creation avoir

**Avoir lie** :
- Type : Total / Partiel (radio)
- Lignes a inclure (checkboxes si partiel)
- Quantites (modifiables si partiel)
- Motif (textarea obligatoire)

**Avoir libre** :
- Client (autocomplete)
- Date avoir
- Motif
- Lignes (collection dynamique)

---

## 6. PDF - Avoir

### 6.1 Titre

```
FACTURE D'AVOIR
```

### 6.2 Reference facture parente

Si avoir lie :
```
Avoir sur facture : FAC-2026-0042 du 15/01/2026
```

### 6.3 Montants

Les montants sont affiches **positifs** avec mention :
```
+------------------------------------------+
|                     Total HT:   500,00 € |
|                     TVA 20%:    100,00 € |
|                     --------------------- |
|            TOTAL A DEDUIRE:    600,00 €  |
+------------------------------------------+
```

### 6.4 Motif

Section dediee pour le motif de l'avoir :
```
Motif de l'avoir :
[Texte saisi par l'utilisateur]
```

---

## 7. Modifications techniques

### 7.1 Migration base de donnees

```sql
-- Nouveaux champs sur facture
ALTER TABLE facture ADD type VARCHAR(20) NOT NULL DEFAULT 'facture';
ALTER TABLE facture ADD facture_parente_id INT DEFAULT NULL;
ALTER TABLE facture MODIFY contrat_id INT DEFAULT NULL;

-- Index et FK
ALTER TABLE facture ADD CONSTRAINT FK_facture_parente
    FOREIGN KEY (facture_parente_id) REFERENCES facture(id);
CREATE INDEX IDX_facture_type ON facture(type);
CREATE INDEX IDX_facture_parente ON facture(facture_parente_id);

-- Mise a jour statut enum (ajouter 'annulee', 'remboursee')
```

### 7.2 Fichiers a creer

| Fichier | Description |
|---------|-------------|
| `src/Form/FactureManuelleType.php` | Formulaire facture ponctuelle |
| `src/Form/AvoirType.php` | Formulaire avoir |
| `templates/facturation/manuelles/index.html.twig` | Liste factures manuelles |
| `templates/facturation/manuelles/new.html.twig` | Creation facture manuelle |
| `templates/facturation/avoirs/index.html.twig` | Liste avoirs |
| `templates/facturation/avoirs/new.html.twig` | Creation avoir libre |
| `templates/facturation/avoirs/new_from_facture.html.twig` | Creation avoir lie |
| `templates/facturation/pdf_avoir.html.twig` | Template PDF avoir |

### 7.3 Fichiers a modifier

| Fichier | Modifications |
|---------|---------------|
| `src/Entity/Facture.php` | Champs type, factureParente, avoirs, statuts |
| `src/Service/FacturationService.php` | Methodes creerFactureManuelle(), creerAvoir() |
| `src/Service/PdfFactureService.php` | Support type avoir |
| `src/Controller/FacturationController.php` | Nouvelles routes |
| `src/Entity/ParametreFacturation.php` | Numerotation unifiee |
| `templates/layout.html.twig` | Menu accordeon |
| `templates/facturation/show.html.twig` | Bouton avoir, section avoirs lies |

---

## 8. Cas de test

### 8.1 Factures ponctuelles

| Test | Resultat attendu |
|------|------------------|
| Creer facture ponctuelle sans contrat | OK, emetteur par defaut utilise |
| Creer facture ponctuelle avec contrat | OK, emetteur du contrat utilise |
| Valider facture ponctuelle | Numero FAC-YYYY-NNNN attribue |
| PDF facture ponctuelle | Genere correctement |

### 8.2 Avoirs lies

| Test | Resultat attendu |
|------|------------------|
| Creer avoir total sur facture validee | Avoir cree, facture -> annulee |
| Creer avoir partiel (50%) | Avoir cree, facture inchangee |
| Creer 2eme avoir partiel (50%) | Avoir cree, facture -> annulee |
| Creer avoir > montant restant | Erreur, montant trop eleve |
| Creer avoir sur brouillon | Erreur, statut invalide |
| Creer avoir sur avoir | Erreur, type invalide |

### 8.3 Avoirs libres

| Test | Resultat attendu |
|------|------------------|
| Creer avoir libre | OK, pas de facture parente |
| Workflow avoir libre | brouillon -> validee -> envoyee -> payee |

### 8.4 Numerotation

| Test | Resultat attendu |
|------|------------------|
| Facture, Facture, Avoir, Facture | FAC-0001, FAC-0002, AV-0003, FAC-0004 |
| Reset annuel | FAC-2027-0001 au 1er janvier |

---

## 9. Estimation

| Phase | Elements |
|-------|----------|
| **Phase 1** | Migration BDD + Entite Facture modifiee |
| **Phase 2** | Factures ponctuelles (service + controller + templates) |
| **Phase 3** | Avoirs lies (service + controller + templates) |
| **Phase 4** | Avoirs libres |
| **Phase 5** | Menu accordeon + navigation |
| **Phase 6** | PDF avoir |
| **Phase 7** | Tests |

---

## 10. Questions ouvertes

*Aucune - Toutes les decisions ont ete prises lors de l'interview.*
