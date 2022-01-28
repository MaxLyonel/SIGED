<?php

namespace Sie\DgesttlaBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class InscriptionDgControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');
    }

    public function testLookasignatures()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/lookAsignatures');
    }

    public function testDoinscriptiondg()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/doInscriptionDg');
    }

}
