<?php

namespace Sie\AppWebBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UpdateSudentLevelControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');
    }

    public function testLookstudentdata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/lookStudentData');
    }

}
