<?php

namespace Sie\RegularBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RestartHourControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');
    }

    public function testLookforrestart()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/lookforRestart');
    }

    public function testRestart()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/restart');
    }

}
