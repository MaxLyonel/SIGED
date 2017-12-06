<?php

namespace Sie\InfraBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SheetTwoControllerTest extends WebTestCase
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
