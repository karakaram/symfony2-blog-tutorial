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
    /**
     * setUp 
     * 
     * @access public
     * @return void
     */
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
     * 
     * @access public
     * @return void
     */
    public function test一覧画面が表示される()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog/');
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    /**
     * test登録ができる 
     * 登録画面の登録処理が正常に機能しているかテストする
     * 
     * @access public
     * @return void
     */
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
        $body = $client->getResponse()->getContent();
        $this->assertSame(1, substr_count($body, '記事を追加しました'));

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

    /**
     * test登録画面のバリデーションが機能する 
     * 登録画面の入力制御が機能しているかテストする
     * 
     * @access public
     * @return void
     */
    public function test登録画面のバリデーションが機能する()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog/new');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $form = $crawler->selectButton('Save Post')->form();
        // $this->登録画面と編集画面のバリデーションが機能する($client, $form);
    }

    /**
     * test詳細画面が表示される 
     * 
     * @access public
     * @return void
     */
    public function test詳細画面が表示される()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog/1/show');
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    /**
     * test削除ができる 
     * 
     * @access public
     * @return void
     */
    public function test削除ができる()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog/1/delete');
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $body = $client->getResponse()->getContent();
        $this->assertSame(1, substr_count($body, '記事を削除しました'));
    }

    /**
     * test編集ができる 
     * 編集画面のデータ更新処理が正常に機能しているかテストする
     * 
     * @access public
     * @return void
     */
    public function test編集ができる()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog/1/edit');
        $this->assertTrue($client->getResponse()->isSuccessful());
        $form = $crawler->selectButton('Save Post')->form();
        $form['form[title]'] = 'edit_title';
        $form['form[body]'] = 'edit_bodybodybody';
        $crawler = $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertTrue($client->getResponse()->isSuccessful());
        $body = $client->getResponse()->getContent();
        $this->assertSame(1, substr_count($body, '記事を編集しました'));

        //データベースを参照し、更新されているか確認
        $kernel = static::createKernel();
        $kernel->boot();
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $dql = 'SELECT p FROM My\BlogBundle\Entity\Post p ORDER BY p.id';
        $query = $em->createQuery($dql);
        $query->setMaxResults(1);
        $posts = $query->execute();
        $post = $posts[0];
        $this->assertSame('edit_title', $post->getTitle());
        $this->assertSame('edit_bodybodybody', $post->getBody());
    }

    /**
     * test編集画面のバリデーションが機能する 
     * 
     * @access public
     * @return void
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
     * @access private
     * @return void
     */
    private function 登録画面と編集画面のバリデーションが機能する(Client $client, Form $form)
    {
        //必須チェック
        $form['form[title]'] = '';
        $form['form[body]'] = '';
        $crawler = $client->submit($form);
        $body = $client->getResponse()->getContent();
        $this->assertSame(2, substr_count($body, 'This value should not be blank'));
        
        //最小文字数チェック
        $form['form[title]'] = '1';
        $form['form[body]'] = '1';
        $crawler = $client->submit($form);
        $body = $client->getResponse()->getContent();
        $this->assertSame(1, substr_count($body, 'This value is too short. It should have 2 characters or more'));
        $this->assertSame(1, substr_count($body, 'This value is too short. It should have 10 characters or more'));
        
        //最大文字数チェック
        $longCharcter = '';
        for ($i=0; $i < 51; $i++) {
            $longCharcter .= 'a';
        }
        $form['form[title]'] = $longCharcter;
        $form['form[body]'] = $longCharcter;
        $crawler = $client->submit($form);
        $body = $client->getResponse()->getContent();
        $this->assertSame(1, substr_count($body, 'This value is too long. It should have 50 characters or less'));
    }

    /**
     * testURLに不正な値を設定した時エラーとなる 
     * 
     * @access public
     * @return void
     */
    public function testURLに不正な値を設定した時NotFoundを返す()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog/a/show');
        $this->assertTrue($client->getResponse()->isNotFound());
        $crawler = $client->request('GET', '/blog/a/delete');
        $this->assertTrue($client->getResponse()->isNotFound());
        $crawler = $client->request('GET', '/blog/a/edit');
        $this->assertTrue($client->getResponse()->isNotFound());
    }
    
}
