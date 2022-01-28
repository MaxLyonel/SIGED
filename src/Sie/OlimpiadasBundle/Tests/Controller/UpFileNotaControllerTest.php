<?php

namespace Sie\OlimpiadasBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UpFileNotaControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');
    }

    public function testUpfileindb()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/upfileInDB');
    }

}
