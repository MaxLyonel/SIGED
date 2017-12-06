<?php

namespace Sie\RegularBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ResetBjpControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');
    }

    public function testLooksie()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/looksie');
    }

    public function testResetbjp()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/resetbjp');
    }

}
