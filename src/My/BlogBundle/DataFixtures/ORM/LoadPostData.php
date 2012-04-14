<?php

namespace My\BlogBundle\DataFixtures\ORM;
 
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use My\BlogBundle\Entity\Post;
 
/**
 * My\BlogBundle\Entity\PostエンティティのFixture
 *
 * @uses Doctrine\Common\DataFixtures\FixtureInterface
 */
class LoadPostData implements FixtureInterface
{
    /**
     * load
     *
     * @param Doctrine\Common\Persistence\ObjectManager $manager
     * @access public
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        $post = new Post();
 
        $post->setTitle('title');
        $post->setBody('bodybodybody');
        $post->setCreatedAt(new \DateTime());
        $post->setUpdatedAt(new \DateTime());
 
        $manager->persist($post);
        $manager->flush();
    }
}
