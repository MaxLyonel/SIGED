<?php

namespace Sie\RegularBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GestionaOperativoEspecialidadControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');
    }

    public function testLookforsie()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/lookforSie');
    }

    public function testChangestatusoperativo()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/changeStatusOperativo');
    }

}
