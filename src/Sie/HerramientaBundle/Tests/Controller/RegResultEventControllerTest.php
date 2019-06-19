<?php

namespace Sie\HerramientaBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegResultEventControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');
    }

    public function testFind()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/find');
    }

}
