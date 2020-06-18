<?php

namespace Sie\AppWebBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RudeUnificationControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');
    }

    public function testLookforstudentshistory()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/lookforStudentsHistory');
    }

}
