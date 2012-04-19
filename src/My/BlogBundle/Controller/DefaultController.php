<?php

namespace My\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use My\BlogBundle\Form\PostType;
use My\BlogBundle\Entity\Post;
use Doctrine\ORM\NoResultException;


class DefaultController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $posts = $em->getRepository('MyBlogBundle:Post')->search();
        return $this->render('MyBlogBundle:Default:index.html.twig', array('posts' => $posts));
    }
    
    public function newAction()
    {
        $form = $this->createForm(new PostType(), new Post());
        return $this->render('MyBlogBundle:Default:new.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function newPostAction()
    {
        $form = $this->createForm(new PostType(), new Post());
        $form->bindRequest($this->getRequest());
        $em = $this->getDoctrine()->getEntityManager();
        if(!$form->isValid()) {
            return $this->render('MyBlogBundle:Default:new.html.twig', array(
                'form' => $form->createView()
            ));
        }
        $em->getRepository('MyBlogBundle:Post')->insert($form->getData());
        $this->get('session')->setFlash('my_blog', '記事を追加しました');
        return $this->redirect($this->generateUrl('blog_index'));
    }

    public function showAction($id)
    {
        try {
            $em = $this->getDoctrine()->getEntityManager();
            $post = $em->getRepository('MyBlogBundle:Post')->searchOneById($id);
            return $this->render('MyBlogBundle:Default:show.html.twig', array('post' => $post));
        } catch (NoResultException $e) {
            throw new NotFoundHttpException('The post does not exist.');
        }
    }

    public function deleteAction($id)
    {
        try {
            $em = $this->getDoctrine()->getEntityManager();
            $em->getRepository('MyBlogBundle:Post')->delete($id);
            $this->get('session')->setFlash('my_blog', '記事を削除しました');
            return $this->redirect($this->generateUrl('blog_index'));
        } catch (NoResultException $e) {
            throw new NotFoundHttpException('The post does not exist.');
        }
    }

    public function editAction($id)
    {
        try {
            $em = $this->getDoctrine()->getEntityManager();
            $post = $em->getRepository('MyBlogBundle:Post')->searchOneById($id);
            $form = $this->createForm(new PostType(), $post);
            return $this->render('MyBlogBundle:Default:edit.html.twig', array(
                'post' => $post,
                'form' => $form->createView(),
            ));
        } catch (NoResultException $e) {
            throw new NotFoundHttpException('The post does not exist.');
        }
    }

    public function editPostAction($id)
    {
        try {
            $em = $this->getDoctrine()->getEntityManager();
            $post = $em->getRepository('MyBlogBundle:Post')->searchOneById($id);
            $form = $this->createForm(new PostType(), $post);
            $form->bindRequest($this->getRequest());
            if (!$form->isValid()) {
                return $this->render('MyBlogBundle:Default:edit.html.twig', array(
                    'post' => $post,
                    'form' => $form->createView(),
                ));
            }
            $post = $em->getRepository('MyBlogBundle:Post')->update($form->getData());
            $this->get('session')->setFlash('my_blog', '記事を編集しました');
            return $this->redirect($this->generateUrl('blog_index'));
        } catch (NoResultException $e) {
            throw new NotFoundHttpException('The post does not exist.');
        }
    }
}
