<?php

namespace App\Tests\Controller\Admin;

use App\Repository\ModuleRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ModuleControllerTest extends WebTestCase
{
    private function loginAsAdmin($client): void
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@factu.local');
        $client->loginUser($testUser);
    }

    public function testModuleListRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/modules');

        $this->assertResponseRedirects('/login');
    }

    public function testModuleListIsAccessible(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/admin/modules');

        $this->assertResponseIsSuccessful();
    }

    public function testModuleListShowsModules(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/admin/modules');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('table', 'Module de base');
    }

    public function testModuleCreateFormIsAccessible(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/admin/modules/nouveau');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testModuleEditFormIsAccessible(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $moduleRepository = static::getContainer()->get(ModuleRepository::class);
        $testModule = $moduleRepository->findOneBy(['nom' => 'Module de base']);

        $client->request('GET', '/admin/modules/' . $testModule->getId() . '/modifier');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }
}
