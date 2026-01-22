<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testLoginWithValidCredentials(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login');
        $client->submitForm('Se connecter', [
            '_username' => 'admin@factu.local',
            '_password' => 'admin123',
        ]);

        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertRouteSame('app_dashboard');
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login');
        $client->submitForm('Se connecter', [
            '_username' => 'admin@factu.local',
            '_password' => 'wrongpassword',
        ]);

        $this->assertResponseRedirects('/login');
    }

    public function testLogout(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@factu.local');

        $client->loginUser($testUser);
        $client->request('GET', '/logout');

        $this->assertResponseRedirects();
    }

    public function testAccessDeniedForAnonymousUser(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseRedirects('/login');
    }
}
