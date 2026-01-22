<?php
/**
 * Script de test Sprint 2 - Parametres
 * Usage: php tests/test_sprint2.php
 */

require dirname(__DIR__).'/vendor/autoload.php';

use App\Entity\Emetteur;
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

echo "=== Tests Sprint 2 - Parametres ===\n\n";

$errors = [];
$success = [];

// Test 1: Emetteur
echo "1. Test Emetteur...\n";
try {
    $emetteurRepo = $em->getRepository(Emetteur::class);
    $emetteur = $emetteurRepo->getOrCreateEmetteur();

    if ($emetteur->getId()) {
        $success[] = "   - Emetteur existant trouve: " . $emetteur->getRaisonSociale();
    } else {
        $emetteur->setRaisonSociale('Test Company');
        $emetteur->setAdresse('123 Test Street');
        $emetteur->setSiren('123456789');
        $emetteur->setEmail('test@test.com');
        $em->persist($emetteur);
        $em->flush();
        $success[] = "   - Nouvel emetteur cree";
    }

    // Test formatage
    $emetteur->setIban('FR7630001007941234567890185');
    $formatted = $emetteur->getIbanFormatted();
    if (str_contains($formatted, ' ')) {
        $success[] = "   - Formatage IBAN OK: $formatted";
    } else {
        $errors[] = "   - Formatage IBAN KO";
    }
} catch (Exception $e) {
    $errors[] = "   - ERREUR: " . $e->getMessage();
}

// Test 2: Module
echo "2. Test Module...\n";
try {
    $moduleRepo = $em->getRepository(Module::class);
    $modules = $moduleRepo->findAllOrderedByName();
    $success[] = "   - " . count($modules) . " module(s) trouve(s)";

    // Test creation
    $testModule = new Module();
    $testModule->setNom('Module Test ' . time());
    $testModule->setPrixDefaut('99.99');
    $testModule->setTauxTva('20.00');
    $testModule->setActif(true);
    $em->persist($testModule);
    $em->flush();
    $success[] = "   - Module cree: " . $testModule->getNom();

    // Test findActifs
    $actifs = $moduleRepo->findActifs();
    $success[] = "   - " . count($actifs) . " module(s) actif(s)";

    // Cleanup
    $em->remove($testModule);
    $em->flush();
    $success[] = "   - Module test supprime";
} catch (Exception $e) {
    $errors[] = "   - ERREUR: " . $e->getMessage();
}

// Test 3: CGV
echo "3. Test CGV...\n";
try {
    $cgvRepo = $em->getRepository(Cgv::class);

    $testCgv = new Cgv();
    $testCgv->setNom('CGV Test ' . time());
    $testCgv->setFichierChemin('test.pdf');
    $testCgv->setFichierOriginal('test.pdf');
    $testCgv->setDateDebut(new DateTime());
    $testCgv->setParDefaut(false);
    $em->persist($testCgv);
    $em->flush();
    $success[] = "   - CGV creee: " . $testCgv->getNom();

    // Test statut
    $statut = $testCgv->getStatut();
    $success[] = "   - Statut CGV: $statut";

    // Cleanup
    $em->remove($testCgv);
    $em->flush();
    $success[] = "   - CGV test supprimee";
} catch (Exception $e) {
    $errors[] = "   - ERREUR: " . $e->getMessage();
}

// Test 4: ParametreFacturation
echo "4. Test ParametreFacturation...\n";
try {
    $paramRepo = $em->getRepository(ParametreFacturation::class);
    $params = $paramRepo->getOrCreateParametres();

    if ($params->getId()) {
        $success[] = "   - Parametres existants trouves";
    } else {
        $em->persist($params);
        $em->flush();
        $success[] = "   - Nouveaux parametres crees";
    }

    // Test generation numero
    $numero = $params->genererNumero();
    $success[] = "   - Numero genere: $numero";

    // Test format
    $params->setFormatNumero('TEST-{YYYY}-{SEQ:4}');
    $params->setProchainNumero(42);
    $numero2 = $params->genererNumero();
    $expected = 'TEST-' . date('Y') . '-0042';
    if ($numero2 === $expected) {
        $success[] = "   - Format numero OK: $numero2";
    } else {
        $errors[] = "   - Format numero KO: attendu $expected, obtenu $numero2";
    }
} catch (Exception $e) {
    $errors[] = "   - ERREUR: " . $e->getMessage();
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
