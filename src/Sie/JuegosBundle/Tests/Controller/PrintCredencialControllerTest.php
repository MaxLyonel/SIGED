<?php

namespace Sie\JuegosBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PrintCredencialControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');
    }

    public function testLookforcredencial()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/lookforCredencial');
    }

    public function testDonwload()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/donwload');
    }

}
