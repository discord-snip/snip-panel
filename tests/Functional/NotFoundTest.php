<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NotFoundTest extends WebTestCase
{
    public function testFetchingSnippet(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/C/helloworld');
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/api/C/byeworld');
        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }
}
