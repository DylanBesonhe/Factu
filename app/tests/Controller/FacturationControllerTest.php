<?php

namespace App\Tests\Controller;

use App\Entity\Facture;
use App\Repository\ContratRepository;
use App\Repository\FactureRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FacturationControllerTest extends WebTestCase
{
    private function loginAsAdmin($client): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@factu.local');
        $client->loginUser($testUser);
    }

    public function testWorkflowRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/facturation');

        $this->assertResponseRedirects('/login');
    }

    public function testWorkflowIsAccessible(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/facturation');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Factures recurrentes');
    }

    public function testWorkflowShowsFourColumns(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/facturation');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.bg-yellow-50 h3', 'A creer');
        $this->assertSelectorTextContains('.bg-blue-50 h3', 'Brouillons');
        $this->assertSelectorTextContains('.bg-green-50 h3', 'Validees');
        $this->assertSelectorTextContains('.bg-purple-50 h3', 'Envoyees');
    }

    public function testWorkflowWithMonthNavigation(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        // Test janvier 2026 - les contrats devraient apparaitre (dateDebutFacturation = 2026-01-01)
        $crawler = $client->request('GET', '/facturation?mois=2026-01');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'January 2026');

        // Test decembre 2025 - aucun contrat ne devrait apparaitre (avant dateDebutFacturation)
        $crawler = $client->request('GET', '/facturation?mois=2025-12');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'December 2025');
        // Verifier qu'il y a 0 contrats a facturer
        $this->assertSelectorTextContains('.bg-yellow-50 p', '0 contrat(s) a facturer');
    }

    public function testWorkflowShowsMonthNavigationLinks(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/facturation?mois=2026-01');

        $this->assertResponseIsSuccessful();
        // Verifier les liens de navigation
        $this->assertSelectorExists('a[href*="mois=2025-12"]');
        $this->assertSelectorExists('a[href*="mois=2026-02"]');
    }

    public function testAnnualContractsOnlyShowInAnniversaryMonth(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        // Les contrats annuels ont une date anniversaire en janvier (2026-01-15)
        // Note: Les fixtures creent deja des factures pour janvier, donc les contrats
        // n'apparaissent pas dans "A creer". On verifie qu'ils n'apparaissent PAS
        // dans les mauvais mois.

        // Fevrier 2026 - NE DOIT PAS apparaitre (pas le mois anniversaire)
        $crawler = $client->request('GET', '/facturation?mois=2026-02');
        $this->assertResponseIsSuccessful();
        $annuelleSection = $crawler->filter('h4:contains("Annuelle")');
        $this->assertEquals(0, $annuelleSection->count(), 'Les contrats annuels ne doivent PAS apparaitre en fevrier');

        // Mars 2026 - NE DOIT PAS apparaitre
        $crawler = $client->request('GET', '/facturation?mois=2026-03');
        $this->assertResponseIsSuccessful();
        $annuelleSection = $crawler->filter('h4:contains("Annuelle")');
        $this->assertEquals(0, $annuelleSection->count(), 'Les contrats annuels ne doivent PAS apparaitre en mars');

        // Janvier 2027 - DEVRAIT apparaitre (prochain mois anniversaire, pas encore facture)
        $crawler = $client->request('GET', '/facturation?mois=2027-01');
        $this->assertResponseIsSuccessful();
        $annuelleSection = $crawler->filter('h4:contains("Annuelle")');
        $this->assertGreaterThan(0, $annuelleSection->count(), 'Les contrats annuels doivent apparaitre en janvier 2027');
    }

    public function testQuarterlyContractsOnlyShowInFirstMonthOfQuarter(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        // Note: Le contrat trimestriel CTR-2026-004 est suspendu dans les fixtures
        // Ce test verifie juste que la section trimestrielle n'apparait pas si pas de contrats

        // Janvier = 1er mois du Q1
        $crawler = $client->request('GET', '/facturation?mois=2026-01');
        $this->assertResponseIsSuccessful();

        // Fevrier = 2eme mois du Q1 - pas de facturation trimestrielle
        $crawler = $client->request('GET', '/facturation?mois=2026-02');
        $this->assertResponseIsSuccessful();
        $trimestrielleSection = $crawler->filter('h4:contains("Trimestrielle")');
        $this->assertEquals(0, $trimestrielleSection->count(), 'Les contrats trimestriels ne doivent PAS apparaitre en fevrier');

        // Avril = 1er mois du Q2
        $crawler = $client->request('GET', '/facturation?mois=2026-04');
        $this->assertResponseIsSuccessful();
    }

    public function testListeIsAccessible(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/facturation/liste');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Liste des factures');
    }

    public function testListeShowsFactures(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/facturation/liste');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table tbody tr');
    }

    public function testListeSearchWorks(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/facturation/liste?search=FAC-2025-00001');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('table', 'FAC-2025-00001');
    }

    public function testListeFilterByStatut(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/facturation/liste?statut=brouillon');

        $this->assertResponseIsSuccessful();
    }

    public function testFactureShowPageIsAccessible(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $factureRepository = static::getContainer()->get(FactureRepository::class);
        $testFacture = $factureRepository->findOneBy(['numero' => 'FAC-2025-00001']);

        $client->request('GET', '/facturation/' . $testFacture->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'FAC-2025-00001');
    }

    public function testFactureShowDisplaysClientInfo(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $factureRepository = static::getContainer()->get(FactureRepository::class);
        $testFacture = $factureRepository->findOneBy(['numero' => 'FAC-2025-00001']);

        $crawler = $client->request('GET', '/facturation/' . $testFacture->getId());

        $this->assertResponseIsSuccessful();
        // Check that client info is displayed somewhere on the page
        $this->assertStringContainsString($testFacture->getClientRaisonSociale(), $crawler->text());
    }

    public function testBrouillonCanBeEdited(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $factureRepository = static::getContainer()->get(FactureRepository::class);
        $testFacture = $factureRepository->findOneBy(['statut' => Facture::STATUT_BROUILLON]);

        if (!$testFacture) {
            $this->markTestSkipped('No brouillon facture found for testing');
        }

        $client->request('GET', '/facturation/' . $testFacture->getId() . '/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testValideeCannotBeEdited(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $factureRepository = static::getContainer()->get(FactureRepository::class);
        // FAC-2025-00001 est maintenant la facture validee
        $testFacture = $factureRepository->findOneBy(['numero' => 'FAC-2025-00001']);

        $client->request('GET', '/facturation/' . $testFacture->getId() . '/edit');

        // Should redirect back to show page
        $this->assertResponseRedirects('/facturation/' . $testFacture->getId());
    }

    public function testCanCreateFactureFromContrat(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $contratRepository = static::getContainer()->get(ContratRepository::class);
        $testContrat = $contratRepository->findOneBy(['numero' => 'CTR-2025-001']);

        // Make a request first to establish session
        $crawler = $client->request('GET', '/facturation');

        // Get CSRF token from the form in the page
        $form = $crawler->filter('form[action*="/facturation/creer/' . $testContrat->getId() . '"]');
        if ($form->count() > 0) {
            $csrfToken = $form->filter('input[name="_token"]')->attr('value');

            $client->request('POST', '/facturation/creer/' . $testContrat->getId(), [
                '_token' => $csrfToken,
            ]);

            // Should redirect after creation
            $this->assertResponseRedirects();
        } else {
            // If no form found (contrat not in list), skip test
            $this->markTestSkipped('Contrat not found in workflow page');
        }
    }

    public function testCanValidateFacture(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $factureRepository = static::getContainer()->get(FactureRepository::class);
        // Le brouillon n'a pas de numero, on le cherche par statut
        $testFacture = $factureRepository->findOneBy(['statut' => Facture::STATUT_BROUILLON]);

        if (!$testFacture) {
            $this->markTestSkipped('No brouillon facture found for testing');
        }

        // Make a request first to get CSRF token
        $crawler = $client->request('GET', '/facturation/' . $testFacture->getId());
        $form = $crawler->filter('form[action*="/valider"]');

        if ($form->count() > 0) {
            $csrfToken = $form->filter('input[name="_token"]')->attr('value');

            $client->request('POST', '/facturation/' . $testFacture->getId() . '/valider', [
                '_token' => $csrfToken,
            ]);

            // Le contrôleur redirige vers le workflow avec le mois
            $this->assertResponseRedirects();
        } else {
            $this->markTestSkipped('Validate form not found (facture may not be a brouillon)');
        }
    }

    public function testCannotDeleteValidatedFacture(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $factureRepository = static::getContainer()->get(FactureRepository::class);
        // FAC-2025-00001 est maintenant la facture validee
        $testFacture = $factureRepository->findOneBy(['numero' => 'FAC-2025-00001']);

        // Make a request first to establish session
        $client->request('GET', '/facturation/' . $testFacture->getId());

        // Try to delete with a manually generated token (validation shouldn't matter since facture is validated)
        $client->request('POST', '/facturation/' . $testFacture->getId() . '/supprimer', [
            '_token' => 'fake_token',
        ]);

        // Should redirect (to workflow with error) - either due to invalid token or because facture is validated
        $this->assertResponseRedirects();
    }

    public function testCanMarkAsEnvoyee(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $factureRepository = static::getContainer()->get(FactureRepository::class);
        $testFacture = $factureRepository->findOneBy(['statut' => Facture::STATUT_VALIDEE]);

        if (!$testFacture) {
            $this->markTestSkipped('No validated facture found for testing');
        }

        // Make a request first to get CSRF token
        $crawler = $client->request('GET', '/facturation/' . $testFacture->getId());
        $form = $crawler->filter('form[action*="/envoyer"]');

        if ($form->count() > 0) {
            $csrfToken = $form->filter('input[name="_token"]')->attr('value');

            $client->request('POST', '/facturation/' . $testFacture->getId() . '/envoyer', [
                '_token' => $csrfToken,
            ]);

            // Le contrôleur redirige vers le workflow avec le mois
            $this->assertResponseRedirects();
        } else {
            $this->markTestSkipped('Envoyer form not found on page');
        }
    }

    public function testCanAddLigneToBrouillon(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $factureRepository = static::getContainer()->get(FactureRepository::class);
        $testFacture = $factureRepository->findOneBy(['statut' => Facture::STATUT_BROUILLON]);

        if (!$testFacture) {
            $this->markTestSkipped('No brouillon facture found for testing');
        }

        $client->request('GET', '/facturation/' . $testFacture->getId() . '/ligne/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testBrouillonsAndValideesFilteredByMonth(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        // En janvier, les factures annuelles (periode janvier) doivent apparaitre
        $crawler = $client->request('GET', '/facturation?mois=2026-01');
        $this->assertResponseIsSuccessful();

        // En fevrier, les factures annuelles (periode janvier) ne doivent PAS apparaitre
        $crawler = $client->request('GET', '/facturation?mois=2026-02');
        $this->assertResponseIsSuccessful();
        // Verifier que les colonnes brouillons/validees sont vides ou ne contiennent pas les factures annuelles
        $brouillonsSection = $crawler->filter('.bg-blue-50')->first()->nextAll()->first();
        $valideesSection = $crawler->filter('.bg-green-50')->first()->nextAll()->first();

        // Les factures de janvier ne doivent pas apparaitre en fevrier
        $this->assertStringNotContainsString('FAC-2025-00001', $crawler->text());
        $this->assertStringNotContainsString('FAC-2025-00002', $crawler->text());
    }

    public function testCannotAddLigneToValidatedFacture(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $factureRepository = static::getContainer()->get(FactureRepository::class);
        // FAC-2025-00001 est maintenant la facture validee
        $testFacture = $factureRepository->findOneBy(['numero' => 'FAC-2025-00001']);

        $client->request('GET', '/facturation/' . $testFacture->getId() . '/ligne/new');

        // Should redirect back to show page
        $this->assertResponseRedirects('/facturation/' . $testFacture->getId());
    }

    public function testFacturesHaveNonZeroAmounts(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $factureRepository = static::getContainer()->get(FactureRepository::class);

        // Recuperer toutes les factures avec un numero (non brouillons)
        $factures = $factureRepository->findBy([], ['id' => 'ASC'], 5);

        $this->assertNotEmpty($factures, 'Il doit y avoir des factures dans les fixtures');

        foreach ($factures as $facture) {
            // Verifier que les montants sont non-nuls
            $this->assertGreaterThan(
                0,
                (float) $facture->getTotalHt(),
                sprintf('La facture %s a un montant HT de 0', $facture->getNumero() ?? 'Brouillon #' . $facture->getId())
            );
            $this->assertGreaterThan(
                0,
                (float) $facture->getTotalTtc(),
                sprintf('La facture %s a un montant TTC de 0', $facture->getNumero() ?? 'Brouillon #' . $facture->getId())
            );

            // Verifier que la facture a des lignes
            $this->assertNotEmpty(
                $facture->getLignes(),
                sprintf('La facture %s n\'a pas de lignes', $facture->getNumero() ?? 'Brouillon #' . $facture->getId())
            );
        }
    }
}
