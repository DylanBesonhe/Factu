<?php

namespace App\Tests\Controller;

use App\Repository\ContratRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContratControllerTest extends WebTestCase
{
    private function loginAsAdmin($client): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@factu.local');
        $client->loginUser($testUser);
    }

    public function testContratListRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/contrats');

        $this->assertResponseRedirects('/login');
    }

    public function testContratListIsAccessible(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/contrats');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Contrats');
    }

    public function testContratListShowsContrats(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/contrats');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table tbody tr');
    }

    public function testContratSearchWorks(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/contrats?search=CTR-2026-001');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('table', 'CTR-2026-001');
    }

    public function testContratCreateFormIsAccessible(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/contrats/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testContratShowPageIsAccessible(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $contratRepository = static::getContainer()->get(ContratRepository::class);
        $testContrat = $contratRepository->findOneBy(['numero' => 'CTR-2026-001']);

        $client->request('GET', '/contrats/' . $testContrat->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'CTR-2026-001');
    }

    public function testContratEditFormIsAccessible(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $contratRepository = static::getContainer()->get(ContratRepository::class);
        $testContrat = $contratRepository->findOneBy(['numero' => 'CTR-2026-001']);

        $client->request('GET', '/contrats/' . $testContrat->getId() . '/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testContratExportCsv(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/contrats/export');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function testContratFilterByStatut(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/contrats?statut=actif');

        $this->assertResponseIsSuccessful();
    }
}
