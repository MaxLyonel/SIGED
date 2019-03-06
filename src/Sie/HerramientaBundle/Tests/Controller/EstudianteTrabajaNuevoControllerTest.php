<?php

namespace Sie\HerramientaBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EstudianteTrabajaNuevoControllerTest extends WebTestCase
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

    public function testResult()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/result');
    }

    public function testDoinscription()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/doInscription');
    }

    public function testList()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/list');
    }

}
