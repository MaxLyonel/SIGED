<?php

namespace Sie\RegularBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class QAHistoryInscriptionControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');
    }

    public function testDoinscription()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/doInscription');
    }

    public function testFillnotas()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/fillNotas');
    }

}
