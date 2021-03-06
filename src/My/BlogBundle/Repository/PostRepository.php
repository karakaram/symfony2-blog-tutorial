<?php

namespace My\BlogBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use My\BlogBundle\Entity\Post;

/**
 * PostRepository
 *
 */
class PostRepository extends EntityRepository
{
    /**
     * search 
     * 
     * @return array Array of PostEntity
     */
    public function search()
    {
        $em = $this->getEntityManager();
        return $em->getRepository('MyBlogBundle:Post')->findAll();
    }

    /**
     * searchOneById 
     * 
     * @param Integer $id 
     * @return \My\BlogBundle\Entity\Post
     * @throws \Doctrine\ORM\NoResultException
     */
    public function searchOneById($id)
    {
        $em = $this->getEntityManager();
        $post = $em->getRepository('MyBlogBundle:Post')->findOneById($id);
        if (empty($post)) {
            throw new NoResultException;
        }
        return $post;
    }

    /**
     * insert 
     * 
     * @param \My\BlogBundle\Entity\Post $post
     * @return Boolean
     */
    public function insert(Post $post)
    {
        $em = $this->getEntityManager('MyBlogBundle:Post');
        $em->persist($post);
        $em->flush();
        return true;
    }

    /**
     * delete 
     * 
     * @param Integer $id 
     * @return Boolean
     * @throws \Doctrine\ORM\NoResultException
     */
    public function delete($id)
    {
        $em = $this->getEntityManager();
        $post = $this->searchOneById($id);
        $em->remove($post);
        $em->flush();
        return true;
    }

    /**
     * update 
     * 
     * @param \My\BlogBundle\Entity\Post $post
     * @param Integer $id 
     * @return Boolean
     * @throws \Doctrine\ORM\NoResultException
     */
    public function update(Post $post)
    {
        $em = $this->getEntityManager();
        $this->searchOneById($post->getId());
        $em->flush();
        return true;
    }
}
