<?php

namespace App\Tests\Controller;

use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ClientControllerTest extends WebTestCase
{
    private function loginAsAdmin($client): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@factu.local');
        $client->loginUser($testUser);
    }

    public function testClientListRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/clients');

        $this->assertResponseRedirects('/login');
    }

    public function testClientListIsAccessible(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/clients');

        $this->assertResponseIsSuccessful();
    }

    public function testClientListShowsClients(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/clients');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table tbody tr');
    }

    public function testClientSearchWorks(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/clients?search=CREATION');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('table', 'CREATION METAL');
    }

    public function testClientCreateFormIsAccessible(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/clients/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testClientShowPageIsAccessible(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $clientRepository = static::getContainer()->get(ClientRepository::class);
        $testClient = $clientRepository->findOneBy(['code' => 'CLI001']);

        $client->request('GET', '/clients/' . $testClient->getId());

        $this->assertResponseIsSuccessful();
    }

    public function testClientEditFormIsAccessible(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $clientRepository = static::getContainer()->get(ClientRepository::class);
        $testClient = $clientRepository->findOneBy(['code' => 'CLI001']);

        $client->request('GET', '/clients/' . $testClient->getId() . '/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testClientExportCsv(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/clients/export');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'text/csv; charset=UTF-8');
    }
}
