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
 
    public function test登録ができる()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog/new');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $form = $crawler->selectButton('Save Post')->form();
        $form['form[title]'] = 'title';
        $form['form[body]'] = 'bodybodybody';
        $crawler = $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
 
        //データベースを参照して登録されているか確認
        $kernel = static::createKernel();
        $kernel->boot();
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $dql = 'SELECT p FROM My\BlogBundle\Entity\Post p ORDER BY p.id DESC';
        $query = $em->createQuery($dql);
        $query->setMaxResults(1);
        $posts = $query->execute();
        $post = $posts[0];
        $this->assertSame('title', $post->getTitle());
        $this->assertSame('bodybodybody', $post->getBody());
    }
    
    public function test登録画面のバリデーションが機能する()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog/new');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $form = $crawler->selectButton('Save Post')->form();
 
        //必須チェック
        $form['form[title]'] = '';
        $form['form[body]'] = '';
        $crawler = $client->submit($form);
        $body = $client->getResponse()->getContent();
        $this->assertSame(2, preg_match_all('/This value should not be blank/', $body, $maches));
 
        //最小文字数チェック
        $form['form[title]'] = '1';
        $form['form[body]'] = '1';
        $crawler = $client->submit($form);
        $body = $client->getResponse()->getContent();
        $this->assertSame(1, preg_match_all('/This value is too short. It should have 2 characters or more/', $body, $maches));
        $this->assertSame(1, preg_match_all('/This value is too short. It should have 10 characters or more/', $body, $maches));
 
        //最大文字数チェック
        $longCharcter = '';
        for ($i=0; $i < 51; $i++) {
            $longCharcter .= 'a';
        }
        $form['form[title]'] = $longCharcter;
        $form['form[body]'] = $longCharcter;
        $crawler = $client->submit($form);
        $body = $client->getResponse()->getContent();
        $this->assertSame(1, preg_match_all('/This value is too long. It should have 50 characters or less/', $body, $maches));
    }

    public function test詳細画面が表示される()
    {
        $client = static::createClient();
        // $crawler = $client->request('GET', '/blog/1/show');
        $crawler = $client->request('GET', '/blog/');
        $link = $crawler->filter('a:contains("title")')->eq(0)->link();
        $crawler = $client->click($link);
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function test一覧画面から削除ができる()
    {
        $client = static::createClient();
        // $crawler = $client->request('GET', '/blog/1/delete');
        $crawler = $client->request('GET', '/blog/');
        $link = $crawler->filter('a:contains("Delete")')->eq(0)->link();
        $crawler = $client->click($link);
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
    }


}
