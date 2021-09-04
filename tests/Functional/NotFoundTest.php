<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotFoundTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/C/helloworld');
        $this->assertResponseIsSuccessful();

        $client->request('GET', '/api/C/byeworld');
        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }
}
