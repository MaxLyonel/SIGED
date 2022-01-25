<?php

namespace Sie\AppWebBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RequestBJPControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');
    }

    public function testLookforstudent()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/lookforStudent');
    }

}
