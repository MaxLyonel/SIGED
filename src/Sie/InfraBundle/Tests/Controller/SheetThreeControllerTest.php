<?php

namespace Sie\InfraBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SheetThreeControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');
    }

    public function testAccess()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/access');
    }

}
