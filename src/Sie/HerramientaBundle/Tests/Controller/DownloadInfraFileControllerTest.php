<?php

namespace Sie\HerramientaBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DownloadInfraFileControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');
    }

    public function testGenerate()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/generate');
    }

    public function testDownloadfile()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/downloadFile');
    }

}
