<?php

namespace Sie\HerramientaBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegularizationCUTControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');
    }

    public function testHistory()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/history');
    }

    public function testRegularization()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/regularization');
    }

}
