<?php

namespace Sie\OlimpiadasBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class InscripcionNivelSuperControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');
    }

    public function testFindsudentinscription()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/findSudentInscription');
    }

}
