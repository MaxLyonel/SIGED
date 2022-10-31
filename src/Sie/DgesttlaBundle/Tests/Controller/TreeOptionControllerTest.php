<?php

namespace Sie\DgesttlaBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TreeOptionControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');
    }

    public function testSeestudents()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/seeStudents');
    }

}
