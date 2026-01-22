<?php

namespace App\Tests\Controller\Admin;

use App\Repository\EmetteurRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EmetteurControllerTest extends WebTestCase
{
    private function loginAsAdmin($client): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@factu.local');
        $client->loginUser($testUser);
    }

    public function testEmetteurListRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/emetteurs');

        $this->assertResponseRedirects('/login');
    }

    public function testEmetteurListIsAccessible(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/admin/emetteurs');

        $this->assertResponseIsSuccessful();
    }

    public function testEmetteurListShowsEmetteurs(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/admin/emetteurs');

        $this->assertResponseIsSuccessful();
        // Check that emetteur from fixtures is displayed
        $this->assertSelectorTextContains('table', 'Factu SAS');
    }

    public function testEmetteurCreateFormIsAccessible(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/admin/emetteurs/nouveau');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testEmetteurShowPageIsAccessible(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $emetteurRepository = static::getContainer()->get(EmetteurRepository::class);
        $testEmetteur = $emetteurRepository->findOneBy(['code' => 'FACTU']);

        $client->request('GET', '/admin/emetteurs/' . $testEmetteur->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Factu SAS');
    }

    public function testEmetteurEditFormIsAccessible(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $emetteurRepository = static::getContainer()->get(EmetteurRepository::class);
        $testEmetteur = $emetteurRepository->findOneBy(['code' => 'FACTU']);

        $client->request('GET', '/admin/emetteurs/' . $testEmetteur->getId() . '/modifier');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testEmetteurVersionFormIsAccessible(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $emetteurRepository = static::getContainer()->get(EmetteurRepository::class);
        $testEmetteur = $emetteurRepository->findOneBy(['code' => 'FACTU']);

        $client->request('GET', '/admin/emetteurs/' . $testEmetteur->getId() . '/version');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testEmetteurParametresFormIsAccessible(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $emetteurRepository = static::getContainer()->get(EmetteurRepository::class);
        $testEmetteur = $emetteurRepository->findOneBy(['code' => 'FACTU']);

        $client->request('GET', '/admin/emetteurs/' . $testEmetteur->getId() . '/parametres');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }
}
