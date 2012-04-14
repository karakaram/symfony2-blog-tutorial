<?php

namespace My\BlogBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function test一覧画面が表示される()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog/');
        $this->assertTrue($client->getResponse()->isSuccessful());
    }
 
    public function test詳細画面が表示される()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog/1/show');
        $this->assertTrue($client->getResponse()->isSuccessful());
    }
}
