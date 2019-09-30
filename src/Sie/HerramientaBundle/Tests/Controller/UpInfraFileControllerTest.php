<?php

namespace Sie\HerramientaBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UpInfraFileControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');
    }

    public function testLookfordata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/lookforData');
    }

}
