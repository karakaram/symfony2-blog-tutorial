<?php

namespace My\BlogBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DomCrawler\Form;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use My\BlogBundle\DataFixtures\ORM\LoadPostData;


/**
 * DefaultControllerTest 
 * 
 * @uses Symfony\Bundle\FrameworkBundle\Test\WebTestCase
 */
class DefaultControllerTest extends WebTestCase
{
    public function setUp()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $loader = new Loader($kernel->getContainer());
        $loader->addFixture(new LoadPostData);
        $fixtures = $loader->getFixtures();
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $purger = new ORMPurger($em);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $executor = new ORMExecutor($em, $purger);
        $executor->execute($fixtures);
    }
    
    /**
     * test一覧画面が表示される 
     */
    public function test一覧画面が表示される()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog/');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $body = $client->getResponse()->getContent();
        $this->assertSame(1, substr_count($body, 'Blog posts'));
    }

    /**
     * test登録ができる 
     * 
     * @return void
     */
    public function test登録ができる()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog/new');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $body = $client->getResponse()->getContent();
        $this->assertSame(1, substr_count($body, 'Add Post'));
        $form = $crawler->selectButton('Save Post')->form();
        $form['post[title]'] = 'title';
        $form['post[body]'] = 'bodybodybody';
        $crawler = $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirection());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $body = $client->getResponse()->getContent();
        $this->assertSame(1, substr_count($body, '記事を追加しました'));
    }

    /**
     * test登録画面のバリデーションが機能する 
     * 登録画面の入力制御が機能しているかテストする
     */
    public function test登録画面のバリデーションが機能する()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog/new');
        $form = $crawler->selectButton('Save Post')->form();
        $this->登録画面と編集画面のバリデーションが機能する($client, $form);
    }

    /**
     * test詳細画面が表示される 
     */
    public function test詳細画面が表示される()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog/1/show');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $body = $client->getResponse()->getContent();
        $this->assertSame(1, substr_count($body, 'bodybodybody'));
    }

    /**
     * test削除ができる 
     */
    public function test削除ができる()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog/1/delete');
        $this->assertTrue($client->getResponse()->isRedirection());
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $body = $client->getResponse()->getContent();
        $this->assertSame(1, substr_count($body, '記事を削除しました'));
    }

    /**
     * test編集ができる 
     * 編集画面のデータ更新処理が正常に機能しているかテストする
     */
    public function test編集ができる()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog/1/edit');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $body = $client->getResponse()->getContent();
        $this->assertSame(1, substr_count($body, 'Edit Post'));
        $form = $crawler->selectButton('Save Post')->form();
        $form['post[title]'] = 'edit_title';
        $form['post[body]'] = 'edit_bodybodybody';
        $crawler = $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $body = $client->getResponse()->getContent();
        $this->assertSame(1, substr_count($body, '記事を編集しました'));
    }

    /**
     * test編集画面のバリデーションが機能する 
     */
    public function test編集画面のバリデーションが機能する()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog/1/edit');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $form = $crawler->selectButton('Save Post')->form();
        $this->登録画面と編集画面のバリデーションが機能する($client, $form);
    }

    /**
     * 登録画面と編集画面のバリデーションが機能する 
     * 
     * @param Symfony\Bundle\FrameworkBundle\Client $client 
     * @param Symfony\Component\DomCrawler\Form $form 
     */
    private function 登録画面と編集画面のバリデーションが機能する(Client $client, Form $form)
    {
        //必須チェック
        $form['post[title]'] = '';
        $form['post[body]'] = '';
        $crawler = $client->submit($form);
        $body = $client->getResponse()->getContent();
        $this->assertSame(2, substr_count($body, 'This value should not be blank'));
    }

    /**
     * testURLに不正な値を設定した時エラーとなる 
     */
    public function testURLに不正な値を設定した時NotFoundを返す()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog/a/show');
        $this->assertTrue($client->getResponse()->isNotFound());
        $crawler = $client->request('GET', '/blog/-1/show');
        $this->assertTrue($client->getResponse()->isNotFound());
        $crawler = $client->request('GET', '/blog/a/delete');
        $this->assertTrue($client->getResponse()->isNotFound());
        $crawler = $client->request('GET', '/blog/-1/delete');
        $this->assertTrue($client->getResponse()->isNotFound());
        $crawler = $client->request('GET', '/blog/a/edit');
        $this->assertTrue($client->getResponse()->isNotFound());
        $crawler = $client->request('GET', '/blog/-1/edit');
        $this->assertTrue($client->getResponse()->isNotFound());
    }
    
}
