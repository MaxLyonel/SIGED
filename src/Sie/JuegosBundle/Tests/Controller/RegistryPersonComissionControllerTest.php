<?php

namespace Sie\JuegosBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistryPersonComissionControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');
    }

    public function testLookfordata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/lookForData');
    }

    public function testNewperson()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/newPerson');
    }

}
