# Specification - Corrections Sprint 2

**Date:** 2026-01-21
**Version:** 1.0
**Statut:** A valider

---

## 1. Contexte et objectifs

### 1.1 Problematique identifiee

Le Sprint 2 actuel presente plusieurs limitations :
- Un seul emetteur possible (mono-entite)
- Pas d'historique des modifications de l'emetteur (RIB, adresse)
- Parametres de facturation globaux au lieu d'etre par emetteur
- CGV non liees aux emetteurs
- Interface ne permettant pas facilement de creer plusieurs elements

### 1.2 Objectifs de la correction

1. **Multi-emetteurs** : Supporter plusieurs entites juridiques ou configurations
2. **Versioning emetteur** : Historiser les changements avec dates d'effet
3. **Parametres par emetteur** : Numerotation et configuration propre a chaque emetteur
4. **CGV par emetteur** : Bibliotheque commune avec association par emetteur
5. **Tracabilite factures** : Chaque facture reference la version exacte de l'emetteur

---

## 2. Modele de donnees revise

### 2.1 Diagramme entite-relation

```
┌─────────────────────┐
│      Emetteur       │
├─────────────────────┤
│ id                  │
│ code (unique)       │──────────────────────┐
│ nom                 │                      │
│ actif               │                      │
│ parDefaut           │                      │
│ createdAt           │                      │
│ updatedAt           │                      │
└─────────────────────┘                      │
         │                                   │
         │ 1:N                               │
         ▼                                   │
┌─────────────────────┐                      │
│  EmetteurVersion    │                      │
├─────────────────────┤                      │
│ id                  │                      │
│ emetteur_id      ───┼──────────────────────┘
│ raisonSociale       │
│ formeJuridique      │
│ capital             │
│ adresse             │
│ siren               │
│ tva                 │
│ email               │
│ telephone           │
│ iban                │
│ bic                 │
│ logo                │
│ dateEffet           │◄─── Date a partir de laquelle cette version est active
│ dateFin             │◄─── NULL si version courante
│ createdAt           │
└─────────────────────┘
         │
         │ Referenced by
         ▼
┌─────────────────────┐
│      Facture        │
├─────────────────────┤
│ ...                 │
│ emetteurVersion_id──┼──── Snapshot des coordonnees au moment de la validation
│ ...                 │
└─────────────────────┘

┌─────────────────────┐
│ ParametreFacturation│
├─────────────────────┤
│ id                  │
│ emetteur_id      ───┼──── Chaque emetteur a ses parametres
│ formatNumero        │
│ prochainNumero      │
│ delaiEcheance       │
│ mentionsLegales     │
│ emailObjet          │
│ emailCorps          │
│ updatedAt           │
└─────────────────────┘

┌─────────────────────┐       ┌─────────────────────┐
│        Cgv          │       │    EmetteurCgv      │
├─────────────────────┤       ├─────────────────────┤
│ id                  │◄──────│ cgv_id              │
│ nom                 │       │ emetteur_id      ───┼──── Association N:N
│ fichierChemin       │       │ parDefaut           │◄─── CGV par defaut pour cet emetteur
│ fichierOriginal     │       │ createdAt           │
│ dateDebut           │       └─────────────────────┘
│ dateFin             │
│ createdAt           │
└─────────────────────┘

┌─────────────────────┐
│       Contrat       │
├─────────────────────┤
│ ...                 │
│ emetteur_id      ───┼──── Emetteur associe au contrat (modifiable)
│ ...                 │
└─────────────────────┘
```

### 2.2 Entites Doctrine

#### Emetteur.php (modifie)
```php
#[ORM\Entity(repositoryClass: EmetteurRepository::class)]
class Emetteur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    private ?string $code = null;  // Ex: "ZK", "KM"

    #[ORM\Column(length: 100)]
    private ?string $nom = null;  // Nom court pour affichage

    #[ORM\Column]
    private bool $actif = true;

    #[ORM\Column]
    private bool $parDefaut = false;

    #[ORM\OneToMany(mappedBy: 'emetteur', targetEntity: EmetteurVersion::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['dateEffet' => 'DESC'])]
    private Collection $versions;

    #[ORM\OneToOne(mappedBy: 'emetteur', targetEntity: ParametreFacturation::class, cascade: ['persist', 'remove'])]
    private ?ParametreFacturation $parametreFacturation = null;

    #[ORM\OneToMany(mappedBy: 'emetteur', targetEntity: EmetteurCgv::class, cascade: ['persist', 'remove'])]
    private Collection $cgvAssociations;

    // Methode utilitaire
    public function getVersionActive(?DateTimeInterface $date = null): ?EmetteurVersion
    {
        $date = $date ?? new DateTime();
        foreach ($this->versions as $version) {
            if ($version->getDateEffet() <= $date &&
                ($version->getDateFin() === null || $version->getDateFin() >= $date)) {
                return $version;
            }
        }
        return null;
    }
}
```

#### EmetteurVersion.php (nouvelle entite)
```php
#[ORM\Entity(repositoryClass: EmetteurVersionRepository::class)]
class EmetteurVersion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'versions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Emetteur $emetteur = null;

    #[ORM\Column(length: 255)]
    private ?string $raisonSociale = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $formeJuridique = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
    private ?string $capital = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $adresse = null;

    #[ORM\Column(length: 9)]
    private ?string $siren = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $tva = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 34, nullable: true)]
    private ?string $iban = null;

    #[ORM\Column(length: 11, nullable: true)]
    private ?string $bic = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $dateEffet = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dateFin = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;
}
```

#### EmetteurCgv.php (nouvelle entite)
```php
#[ORM\Entity(repositoryClass: EmetteurCgvRepository::class)]
class EmetteurCgv
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'cgvAssociations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Emetteur $emetteur = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cgv $cgv = null;

    #[ORM\Column]
    private bool $parDefaut = false;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;
}
```

#### ParametreFacturation.php (modifie)
```php
#[ORM\Entity(repositoryClass: ParametreFacturationRepository::class)]
class ParametreFacturation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'parametreFacturation')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Emetteur $emetteur = null;  // NOUVEAU: lie a l'emetteur

    #[ORM\Column(length: 50)]
    private ?string $formatNumero = 'FA-{YYYY}-{SEQ:5}';

    #[ORM\Column]
    private int $prochainNumero = 1;

    #[ORM\Column]
    private int $delaiEcheance = 30;

    // ... autres champs inchanges
}
```

---

## 3. Interface utilisateur

### 3.1 Liste des emetteurs (`/admin/emetteurs`)

```
┌─────────────────────────────────────────────────────────────────────┐
│ Gestion des emetteurs                           [+ Nouvel emetteur] │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────────┐│
│  │ Code │ Nom           │ Raison sociale  │ Statut │ Actions      ││
│  ├──────┼───────────────┼─────────────────┼────────┼──────────────┤│
│  │ ZK   │ Zephir Kemeo  │ Zephir Kemeo SA │ Actif  │ Voir Modifier││
│  │      │               │                 │ Defaut │              ││
│  ├──────┼───────────────┼─────────────────┼────────┼──────────────┤│
│  │ KM   │ Kemeo         │ Kemeo SARL      │ Actif  │ Voir Modifier││
│  └──────┴───────────────┴─────────────────┴────────┴──────────────┘│
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

### 3.2 Fiche emetteur avec onglets (`/admin/emetteurs/{id}`)

```
┌─────────────────────────────────────────────────────────────────────┐
│ Zephir Kemeo (ZK)                        [Modifier] [Desactiver]    │
├─────────────────────────────────────────────────────────────────────┤
│ [Informations] [Versions] [Parametres facturation] [CGV]            │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ONGLET: Informations (version active)                              │
│  ─────────────────────────────────────                              │
│  Raison sociale: Zephir Kemeo SA                                    │
│  Forme juridique: SA                                                │
│  SIREN: 123 456 789                                                 │
│  Adresse: 18 rue Saint Nicolas, 75001 Paris                         │
│  Email: contact@zephir-kemeo.fr                                     │
│  IBAN: FR76 3000 1007 9412 3456 7890 185                            │
│                                                                     │
│  Version active depuis: 01/01/2026                                  │
│                                    [Creer une nouvelle version]     │
│                                                                     │
├─────────────────────────────────────────────────────────────────────┤
│  ONGLET: Versions                                                   │
│  ─────────────────                                                  │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │ Date effet │ Date fin   │ Raison sociale  │ IBAN (4 derniers)  │ │
│  ├────────────┼────────────┼─────────────────┼────────────────────┤ │
│  │ 01/01/2026 │ -          │ Zephir Kemeo SA │ ...0185 (actuelle) │ │
│  │ 01/06/2025 │ 31/12/2025 │ Zephir Kemeo SA │ ...0142            │ │
│  │ 01/01/2025 │ 31/05/2025 │ Zephir Kemeo    │ ...0142            │ │
│  └────────────┴────────────┴─────────────────┴────────────────────┘ │
│                                                                     │
├─────────────────────────────────────────────────────────────────────┤
│  ONGLET: Parametres facturation                                     │
│  ──────────────────────────────                                     │
│  Format numero: ZK-{YYYY}-{SEQ:5}                                   │
│  Prochain numero: 42                                                │
│  Apercu: ZK-2026-00042                                              │
│                                                                     │
│  Delai echeance: 30 jours                                           │
│  Mentions legales: [...]                                            │
│                                         [Modifier les parametres]   │
│                                                                     │
├─────────────────────────────────────────────────────────────────────┤
│  ONGLET: CGV                                                        │
│  ───────────                                                        │
│  CGV associees a cet emetteur:                                      │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │ Version    │ Periode          │ Defaut │ Actions              │ │
│  ├────────────┼──────────────────┼────────┼──────────────────────┤ │
│  │ CGV 2026   │ 01/01/2026 - ... │   ✓    │ Telecharger Dissocier│ │
│  │ CGV 2025   │ 01/01/2025 - ... │        │ Telecharger Dissocier│ │
│  └────────────┴──────────────────┴────────┴──────────────────────┘ │
│                                      [Associer une CGV existante]   │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

### 3.3 Formulaire nouvelle version

```
┌─────────────────────────────────────────────────────────────────────┐
│ Nouvelle version - Zephir Kemeo                                     │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  Date d'effet: [01/02/2026    ] (date a partir de laquelle          │
│                                  cette version sera active)         │
│                                                                     │
│  ─────────────────────────────────────────────────────────────────  │
│  Les champs ci-dessous sont pre-remplis avec la version actuelle.   │
│  Modifiez uniquement ce qui change.                                 │
│  ─────────────────────────────────────────────────────────────────  │
│                                                                     │
│  Raison sociale: [Zephir Kemeo SA_______________________________]   │
│  Forme juridique: [SA___________]  Capital: [50000.00___] EUR       │
│                                                                     │
│  Adresse:                                                           │
│  [18 rue Saint Nicolas_________________________________________]    │
│  [75001 Paris__________________________________________________]    │
│                                                                     │
│  SIREN: [123456789] TVA: [FR12345678901_____]                       │
│                                                                     │
│  Contact:                                                           │
│  Email: [contact@zephir-kemeo.fr_______] Tel: [01 23 45 67 89]      │
│                                                                     │
│  Coordonnees bancaires:                                             │
│  IBAN: [FR76 3000 1007 9412 3456 7890 185_______________________]   │
│  BIC:  [BNPAFRPP__]                                                 │
│                                                                     │
│                              [Annuler]  [Creer cette version]       │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

### 3.4 Bibliotheque CGV (`/admin/cgv`)

L'ecran CGV devient une bibliotheque commune. L'association aux emetteurs se fait depuis la fiche emetteur.

```
┌─────────────────────────────────────────────────────────────────────┐
│ Bibliotheque CGV                                    [+ Nouvelle CGV]│
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────────┐│
│  │ Version  │ Fichier    │ Periode         │ Emetteurs   │ Actions ││
│  ├──────────┼────────────┼─────────────────┼─────────────┼─────────┤│
│  │ CGV 2026 │ cgv_26.pdf │ 01/01/26 - ...  │ ZK, KM      │ ... ... ││
│  │ CGV 2025 │ cgv_25.pdf │ 01/01/25 - ...  │ ZK          │ ... ... ││
│  └──────────┴────────────┴─────────────────┴─────────────┴─────────┘│
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 4. Regles metier

### 4.1 Gestion des versions emetteur

1. **Creation de version**
   - L'utilisateur definit la date d'effet (peut etre dans le futur)
   - La version precedente voit sa `dateFin` automatiquement ajustee a `dateEffet - 1 jour`
   - Les champs sont pre-remplis avec la version actuelle

2. **Selection de version pour facturation**
   - Lors de la validation d'une facture, le systeme selectionne la version active a la date d'emission
   - La facture stocke l'ID de la version utilisee (snapshot)

3. **Chevauchement interdit**
   - Deux versions d'un meme emetteur ne peuvent pas avoir des periodes qui se chevauchent
   - Le systeme ajuste automatiquement les dates

### 4.2 Emetteur par defaut

1. Un seul emetteur peut etre marque `parDefaut = true`
2. Quand un emetteur est marque par defaut, les autres sont automatiquement demarques
3. Le contrat herite de l'emetteur par defaut s'il n'est pas specifie
4. La facture herite de l'emetteur du contrat, modifiable en brouillon

### 4.3 CGV et emetteurs

1. Les CGV sont stockees dans une bibliotheque commune
2. Chaque emetteur peut etre associe a plusieurs CGV
3. Une CGV peut etre partagee entre plusieurs emetteurs
4. Chaque emetteur a une CGV par defaut parmi celles associees

### 4.4 Desactivation emetteur

1. Un emetteur desactive (`actif = false`) :
   - N'apparait plus dans les listes de selection pour nouveaux contrats/factures
   - Reste visible dans les contrats/factures existants
   - Peut etre reactive par un administrateur

---

## 5. Routes et controllers

### 5.1 Nouvelles routes

| Route | Methode | URL | Description |
|-------|---------|-----|-------------|
| app_admin_emetteurs | GET | /admin/emetteurs | Liste des emetteurs |
| app_admin_emetteurs_new | GET/POST | /admin/emetteurs/nouveau | Creer emetteur |
| app_admin_emetteurs_show | GET | /admin/emetteurs/{id} | Fiche emetteur |
| app_admin_emetteurs_edit | GET/POST | /admin/emetteurs/{id}/modifier | Modifier emetteur |
| app_admin_emetteurs_toggle | POST | /admin/emetteurs/{id}/toggle | Activer/desactiver |
| app_admin_emetteurs_default | POST | /admin/emetteurs/{id}/defaut | Definir par defaut |
| app_admin_emetteurs_version_new | GET/POST | /admin/emetteurs/{id}/version | Nouvelle version |
| app_admin_emetteurs_params | GET/POST | /admin/emetteurs/{id}/parametres | Params facturation |
| app_admin_emetteurs_cgv | GET | /admin/emetteurs/{id}/cgv | CGV associees |
| app_admin_emetteurs_cgv_add | POST | /admin/emetteurs/{id}/cgv/ajouter | Associer CGV |
| app_admin_emetteurs_cgv_remove | POST | /admin/emetteurs/{id}/cgv/{cgvId}/retirer | Dissocier CGV |

### 5.2 Routes modifiees

| Route | Modification |
|-------|--------------|
| app_admin_emetteur | Supprimee (remplacee par app_admin_emetteurs) |
| app_admin_facturation | Supprimee (integree dans fiche emetteur) |
| app_admin_cgv | Devient bibliotheque commune (sans association directe) |

---

## 6. Migration des donnees

### 6.1 Strategie

L'utilisateur a choisi de **repartir de zero**. Les tables seront videes et recreees.

### 6.2 Script de migration

```sql
-- Suppression des anciennes donnees
TRUNCATE TABLE parametre_facturation;
TRUNCATE TABLE cgv;
TRUNCATE TABLE module;
TRUNCATE TABLE emetteur;

-- Les nouvelles tables seront creees par la migration Doctrine
```

---

## 7. Variables de numerotation

Le format de numero supporte les variables suivantes :

| Variable | Description | Exemple |
|----------|-------------|---------|
| {YYYY} | Annee sur 4 chiffres | 2026 |
| {YY} | Annee sur 2 chiffres | 26 |
| {MM} | Mois sur 2 chiffres | 01 |
| {SEQ:N} | Sequence sur N chiffres | 00042 |
| {CODE} | Code de l'emetteur | ZK |
| {SIREN} | SIREN de l'emetteur | 123456789 |

Exemple: `{CODE}-{YYYY}-{SEQ:5}` → `ZK-2026-00042`

---

## 8. Impact sur les autres sprints

### 8.1 Sprint 3 - Clients
- Le contrat devra avoir un champ `emetteur_id`
- La fiche client affichera les contrats groupes par emetteur

### 8.2 Sprint 6 - Facturation
- La facture stockera `emetteur_version_id`
- Le workflow de facturation permettra de changer l'emetteur en brouillon
- Le PDF utilisera les coordonnees de la version referencee

### 8.3 Sprint 7 - Dashboard
- Filtre par emetteur sur toutes les statistiques
- Vue consolidee par defaut

---

## 9. Checklist d'implementation

- [ ] Creer l'entite EmetteurVersion
- [ ] Creer l'entite EmetteurCgv
- [ ] Modifier l'entite Emetteur (ajouter code, nom, parDefaut, relations)
- [ ] Modifier l'entite ParametreFacturation (ajouter relation emetteur)
- [ ] Modifier l'entite Cgv (retirer parDefaut, ajouter compteur emetteurs)
- [ ] Generer et appliquer les migrations
- [ ] Creer EmetteurController avec toutes les actions
- [ ] Creer les formulaires (EmetteurType, EmetteurVersionType)
- [ ] Creer les templates (liste, fiche avec onglets, formulaires)
- [ ] Mettre a jour la sidebar (Emetteurs au lieu de Emetteur)
- [ ] Supprimer l'ancien ecran /admin/emetteur
- [ ] Supprimer l'ancien ecran /admin/facturation
- [ ] Adapter l'ecran CGV en bibliotheque
- [ ] Ecrire les tests

---

## 10. Questions ouvertes

Aucune question en suspens. La specification est complete.
