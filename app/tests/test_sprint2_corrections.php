<?php
/**
 * Script de test Sprint 2 Corrections - Multi-emetteurs avec versioning
 * Usage: php tests/test_sprint2_corrections.php
 */

require dirname(__DIR__).'/vendor/autoload.php';

use App\Entity\Emetteur;
use App\Entity\EmetteurVersion;
use App\Entity\EmetteurCgv;
use App\Entity\Module;
use App\Entity\Cgv;
use App\Entity\ParametreFacturation;
use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

// Load env
(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

// Boot kernel
$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();

echo "=== Tests Sprint 2 Corrections - Multi-emetteurs ===\n\n";

$errors = [];
$success = [];

// Test 1: Emetteur creation
echo "1. Test creation Emetteur...\n";
try {
    $emetteur = new Emetteur();
    $emetteur->setCode('TEST');
    $emetteur->setNom('Emetteur Test');
    $emetteur->setActif(true);
    $emetteur->setParDefaut(true);
    $em->persist($emetteur);
    $em->flush();
    $success[] = "   - Emetteur cree: " . $emetteur->getCode() . " - " . $emetteur->getNom();
} catch (Exception $e) {
    $errors[] = "   - ERREUR Emetteur: " . $e->getMessage();
}

// Test 2: EmetteurVersion creation
echo "2. Test creation EmetteurVersion...\n";
try {
    $version = new EmetteurVersion();
    $version->setRaisonSociale('Test Company SA');
    $version->setFormeJuridique('SA');
    $version->setCapital('50000.00');
    $version->setAdresse("10 rue du Test\n75001 Paris");
    $version->setSiren('123456789');
    $version->setTva('FR12345678901');
    $version->setEmail('test@example.com');
    $version->setTelephone('01 23 45 67 89');
    $version->setIban('FR7630001007941234567890185');
    $version->setBic('BNPAFRPP');
    $version->setDateEffet(new DateTime());
    $emetteur->addVersion($version); // Use proper method to maintain bidirectional relation
    $em->persist($version);
    $em->flush();
    $success[] = "   - Version creee: " . $version->getRaisonSociale();
    $success[] = "   - IBAN formate: " . $version->getIbanFormatted();
    $success[] = "   - SIREN formate: " . $version->getSirenFormatted();
} catch (Exception $e) {
    $errors[] = "   - ERREUR Version: " . $e->getMessage();
}

// Test 3: Version active
echo "3. Test version active...\n";
try {
    $versionActive = $emetteur->getVersionActive();
    if ($versionActive && $versionActive->getRaisonSociale() === 'Test Company SA') {
        $success[] = "   - Version active OK: " . $versionActive->getRaisonSociale();
    } else {
        $errors[] = "   - Version active KO";
    }
} catch (Exception $e) {
    $errors[] = "   - ERREUR Version active: " . $e->getMessage();
}

// Test 4: ParametreFacturation with Emetteur
echo "4. Test ParametreFacturation...\n";
try {
    $params = new ParametreFacturation();
    $params->setEmetteur($emetteur);
    $params->setFormatNumero('{CODE}-{YYYY}-{SEQ:5}');
    $params->setProchainNumero(1);
    $params->setDelaiEcheance(30);
    $em->persist($params);
    $em->flush();

    $numero = $params->genererNumero();
    $expected = 'TEST-' . date('Y') . '-00001';
    if ($numero === $expected) {
        $success[] = "   - Numero genere OK: $numero";
    } else {
        $errors[] = "   - Numero genere KO: attendu $expected, obtenu $numero";
    }
} catch (Exception $e) {
    $errors[] = "   - ERREUR Parametres: " . $e->getMessage();
}

// Test 5: CGV sans parDefaut (bibliotheque)
echo "5. Test CGV bibliotheque...\n";
try {
    $cgv = new Cgv();
    $cgv->setNom('CGV Test 2026');
    $cgv->setFichierChemin('test.pdf');
    $cgv->setFichierOriginal('test.pdf');
    $cgv->setDateDebut(new DateTime());
    $em->persist($cgv);
    $em->flush();
    $success[] = "   - CGV creee: " . $cgv->getNom();
    $success[] = "   - Periode: " . $cgv->getPeriode();
    $success[] = "   - Statut: " . $cgv->getStatut();
} catch (Exception $e) {
    $errors[] = "   - ERREUR CGV: " . $e->getMessage();
}

// Test 6: EmetteurCgv association
echo "6. Test association EmetteurCgv...\n";
try {
    $assoc = new EmetteurCgv();
    $assoc->setCgv($cgv);
    $assoc->setParDefaut(true);
    $emetteur->addCgvAssociation($assoc); // Use proper method to maintain bidirectional relation
    $em->persist($assoc);
    $em->flush();
    $success[] = "   - Association creee";
    $success[] = "   - CGV par defaut: " . ($assoc->isParDefaut() ? 'Oui' : 'Non');

    // Test getCgvDefaut
    $cgvDefaut = $emetteur->getCgvDefaut();
    if ($cgvDefaut && $cgvDefaut->getNom() === 'CGV Test 2026') {
        $success[] = "   - getCgvDefaut() OK: " . $cgvDefaut->getNom();
    } else {
        $errors[] = "   - getCgvDefaut() KO";
    }
} catch (Exception $e) {
    $errors[] = "   - ERREUR Association: " . $e->getMessage();
}

// Test 7: Module (inchange)
echo "7. Test Module...\n";
try {
    $module = new Module();
    $module->setNom('Module Test');
    $module->setPrixDefaut('99.99');
    $module->setTauxTva('20.00');
    $module->setActif(true);
    $em->persist($module);
    $em->flush();
    $success[] = "   - Module cree: " . $module->getNom();
} catch (Exception $e) {
    $errors[] = "   - ERREUR Module: " . $e->getMessage();
}

// Cleanup
echo "\n8. Nettoyage...\n";
try {
    $em->remove($module);
    $em->remove($assoc);
    $em->remove($cgv);
    $em->remove($params);
    $em->remove($version);
    $em->remove($emetteur);
    $em->flush();
    $success[] = "   - Donnees de test supprimees";
} catch (Exception $e) {
    $errors[] = "   - ERREUR Nettoyage: " . $e->getMessage();
}

// Resume
echo "\n=== RESUME ===\n";
echo "Succes: " . count($success) . "\n";
foreach ($success as $s) {
    echo "  [OK] $s\n";
}

if (count($errors) > 0) {
    echo "\nErreurs: " . count($errors) . "\n";
    foreach ($errors as $e) {
        echo "  [KO] $e\n";
    }
    exit(1);
} else {
    echo "\n[OK] Tous les tests sont passes!\n";
    exit(0);
}
