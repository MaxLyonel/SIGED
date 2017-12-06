<?php

namespace Sie\RegularBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ListCloseRudeControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');
    }

    public function testEnable()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/enable');
    }

}
