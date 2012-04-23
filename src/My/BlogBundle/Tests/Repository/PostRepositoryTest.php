<?php

namespace My\BlogBundle\Tests\Repository;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\DoctrineFixturesBundle\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use My\BlogBundle\DataFixtures\ORM\LoadPostData;
use My\BlogBundle\Entity\Post;


class PostRepositoryTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \My\BlogBundle\Repository\PostRepository
     */
    private $postRepository;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    public function setUp()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $this->container = $kernel->getContainer();
        $loader = new Loader($this->container);
        $loader->addFixture(new LoadPostData);
        $fixtures = $loader->getFixtures();
        $this->em = $this->container->get('doctrine.orm.entity_manager');
        $purger = new ORMPurger($this->em);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $executor = new ORMExecutor($this->em, $purger);
        $executor->execute($fixtures);

        $this->postRepository = $this->em->getRepository('MyBlogBundle:Post');
    }

    public function testSearch()
    {
        $posts = $this->postRepository->search();
        $post = $posts[0];
        $this->assertSame($post->getTitle(), 'title');
    }

    public function testSearchOneById()
    {
        $post = $this->postRepository->searchOneById(1);
        $this->assertSame($post->getTitle(), 'title');
    }

    public function testInsert()
    {
        $post = new Post();
        $post->setTitle('title');
        $post->setBody('bodybodybody');
        $this->assertTrue($this->postRepository->insert($post));

        $query = $this->postRepository
            ->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->setMaxResults(1);
        $posts = $query->getResult();
        $post = $posts[0];
        $this->assertSame('title', $post->getTitle());
        $this->assertSame('bodybodybody', $post->getBody());
    }

    public function testDelete()
    {
        $this->assertTrue($this->postRepository->delete(1));
        
        $query = $this->postRepository
            ->createQueryBuilder('p')
            ->where('p.id = :id')
            ->setParameter('id', 1)
            ->getQuery();
        $posts = $query->getResult();
        $this->assertSame(array(), $posts);
    }

    public function testUpdate()
    {
        $post = new Post();
        $post = $this->postRepository->searchOneById(1);
        $post->setTitle('edit_title');
        $post->setBody('edit_bodybodybody');
        $this->assertTrue($this->postRepository->update($post));
        
        $query = $this->postRepository
            ->createQueryBuilder('p')
            ->where('p.id = :id')
            ->setParameter('id', 1)
            ->getQuery();
        $posts = $query->getResult();
        $post = $posts[0];
        $this->assertSame('edit_title', $post->getTitle());
        $this->assertSame('edit_bodybodybody', $post->getBody());
    }

}
