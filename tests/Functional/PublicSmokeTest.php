<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PublicSmokeTest extends WebTestCase
{
    public function testPublicPagesRespond(): void
    {
        $client = static::createClient();

        foreach (['/', '/elections', '/agent/login', '/login'] as $path) {
            $client->request('GET', $path);
            self::assertResponseIsSuccessful(sprintf('GET %s should succeed', $path));
        }

        // Unauthenticated users are sent to the voter sign-in for the profile area.
        $client->request('GET', '/profile');
        self::assertResponseRedirects('/login');
    }
}
