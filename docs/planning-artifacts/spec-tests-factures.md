# Specifications des Tests End-to-End - Factures

**Date:** 2026-01-22
**Basé sur:** Exemples Factures (CREATION METAL, GENERIX GROUP)

---

## Structure des Factures

### Informations Emetteur
| Champ | Valeur exemple |
|-------|----------------|
| Raison sociale | KEMEO |
| Forme juridique | SAS, société par actions simplifiée |
| Capital | 129 120,00 € |
| SIREN | 808.182.521 |
| N° TVA | FR80808182521 |
| Adresse | 18 RUE SAINT NICOLAS, 75012 PARIS 12 - France |
| Email | comptabilite@kemeo.com |
| Téléphone | +33176350486 |
| Logo | KAMMI par KEMEO |

### Format Numéro Facture
Pattern identifié: `30{YY}{MM}01-{SEQ}`
- Exemple: `30202601-6321` = 30 + 2026 + 01 + 01 + "-" + 6321

### Informations Client
| Champ | Exemple 1 | Exemple 2 |
|-------|-----------|-----------|
| Raison sociale | CREATION METAL | GENERIX GROUP |
| SIREN | 531588804 | 377619150 |
| N° TVA | FR94531588804 | FR88377619150 |
| Adresse | 5 IMPASSE DES METIERS, 79240 MONCOUTANT-SUR-SEVRE | Service comptabilité fournisseurs, 2 RUE DES PEUPLIERS, 59810 LESQUIN |
| Email | contact@creation-metal.eu | p-klebinder@ddslogistics.com |

---

## Cas de Test 1: Facture Simple (CREATION METAL)

### Données d'entrée

```yaml
facture:
  numero: "30202601-6321"
  date_emission: "2026-01-21"
  date_echeance: "2026-01-21"
  type_vente: "Prestations de services"

client:
  raison_sociale: "CREATION METAL"
  siren: "531588804"
  adresse: "5 IMPASSE DES METIERS"
  code_postal: "79240"
  ville: "MONCOUTANT-SUR-SEVRE"
  pays: "France"
  email: "contact@creation-metal.eu"
  tva_intra: "FR94531588804"

lignes:
  - module: "Module Congés & Absences"
    quantite: 20
    prix_unitaire_ht: 2.75
    taux_tva: 20
    remise_pourcent: 0

  - module: "Module Temps & Activités"
    quantite: 20
    prix_unitaire_ht: 2.75
    taux_tva: 20
    remise_pourcent: 0

  - module: "Module Certification"
    quantite: 20
    prix_unitaire_ht: 0.45
    taux_tva: 20
    remise_pourcent: 0

paiement:
  moyen: "Prélèvement SEPA"
```

### Résultats attendus

```yaml
calculs:
  ligne_1:
    total_ht: 55.00  # 20 × 2.75
  ligne_2:
    total_ht: 55.00  # 20 × 2.75
  ligne_3:
    total_ht: 9.00   # 20 × 0.45

totaux:
  total_ht: 119.00
  total_tva: 23.80   # 119.00 × 20%
  total_ttc: 142.80

details_tva:
  - taux: 20
    base_ht: 119.00
    montant_tva: 23.80
```

---

## Cas de Test 2: Facture avec Remises (GENERIX GROUP)

### Données d'entrée

```yaml
facture:
  numero: "30202601-6316"
  date_emission: "2026-01-21"
  date_echeance: "2026-01-21"
  mention_speciale: "Instance : https://ddslogistics.kammi.pro/"

client:
  raison_sociale: "GENERIX GROUP"
  siren: "377619150"
  adresse: "Service comptabilité fournisseurs\n2 RUE DES PEUPLIERS"
  code_postal: "59810"
  ville: "LESQUIN"
  pays: "France"
  email: "p-klebinder@ddslogistics.com"
  tva_intra: "FR88377619150"

lignes:
  - module: "Module Entretiens & Evaluations"
    quantite: 119
    prix_unitaire_ht: 1.53
    taux_tva: 20
    remise_pourcent: 33

  - module: "Module Administration du Personnel"
    quantite: 119
    prix_unitaire_ht: 1.83
    taux_tva: 20
    remise_pourcent: 0

  - module: "Module Congés & Absences"
    quantite: 119
    prix_unitaire_ht: 1.83
    taux_tva: 20
    remise_pourcent: 0

  - module: "Module Notes de frais"
    quantite: 119
    prix_unitaire_ht: 1.53
    taux_tva: 20
    remise_pourcent: 0

  - module: "Module Temps & Activités"
    quantite: 119
    prix_unitaire_ht: 2.36
    taux_tva: 20
    remise_pourcent: 0

remise_globale:
  pourcent: 5

paiement:
  moyen: "Prélèvement SEPA"
```

### Résultats attendus

```yaml
calculs:
  ligne_1:
    brut_ht: 182.07      # 119 × 1.53
    remise: 60.08        # 182.07 × 33%
    total_ht: 121.99     # 182.07 - 60.08
  ligne_2:
    total_ht: 217.77     # 119 × 1.83
  ligne_3:
    total_ht: 217.77     # 119 × 1.83
  ligne_4:
    total_ht: 182.07     # 119 × 1.53
  ligne_5:
    total_ht: 280.84     # 119 × 2.36

totaux:
  total_ht_avant_remise: 1020.44  # Somme des lignes
  remise_globale_pourcent: 5
  remise_globale_montant: 51.02   # 1020.44 × 5%
  total_ht: 969.42                # 1020.44 - 51.02
  total_tva: 193.88               # 969.42 × 20%
  total_ttc: 1163.30

details_tva:
  - taux: 20
    base_ht: 969.42
    montant_tva: 193.88
```

---

## Tests Fonctionnels à Implémenter

### Test 1: Génération Facture Simple
```php
public function testGenerationFactureSimple(): void
{
    // Créer client CREATION METAL
    // Créer contrat avec 3 modules
    // Générer la facture
    // Vérifier les calculs
    // Vérifier le PDF généré
}
```

### Test 2: Génération Facture avec Remises
```php
public function testGenerationFactureAvecRemises(): void
{
    // Créer client GENERIX GROUP
    // Créer contrat avec 5 modules
    // Appliquer remise ligne (33%)
    // Appliquer remise globale (5%)
    // Générer la facture
    // Vérifier les calculs
    // Vérifier le PDF généré
}
```

### Test 3: Validation Calculs TVA
```php
public function testCalculsTva(): void
{
    // Vérifier que Base HT × Taux = Montant TVA
    // Vérifier que Total HT + Total TVA = Total TTC
    // Vérifier les arrondis (2 décimales)
}
```

### Test 4: Format Numéro Facture
```php
public function testFormatNumeroFacture(): void
{
    // Vérifier le pattern 30{YY}{MM}01-{SEQ}
    // Vérifier l'incrémentation du numéro
    // Vérifier l'unicité
}
```

### Test 5: Mentions Légales
```php
public function testMentionsLegales(): void
{
    // Vérifier présence pénalités de retard
    // Vérifier indemnité forfaitaire 40€
    // Vérifier infos émetteur en pied de page
}
```

---

## Prérequis pour l'Implémentation

1. **Sprint 4 - Contrats** (doit être terminé)
   - Entité Contrat avec lignes tarifaires
   - Modules associés au contrat
   - Prix unitaires et remises par ligne

2. **Sprint 6 - Facturation** (à implémenter)
   - Entité Facture
   - Entité LigneFacture
   - Service de calcul (HT, TVA, TTC, remises)
   - Service de génération PDF (DomPDF)
   - Template PDF avec mise en page

---

## Modules Identifiés (à ajouter dans la table Module)

| Nom du module | Prix exemple |
|---------------|--------------|
| Module Congés & Absences | 1,83 € - 2,75 € |
| Module Temps & Activités | 2,36 € - 2,75 € |
| Module Certification | 0,45 € |
| Module Entretiens & Evaluations | 1,53 € |
| Module Administration du Personnel | 1,83 € |
| Module Notes de frais | 1,53 € |

---

## Notes Techniques

- Tous les montants sont en EUR
- Arrondi à 2 décimales
- TVA à 20% (taux standard France)
- Paiement par prélèvement SEPA
- Format date: "21 jan. 2026"
