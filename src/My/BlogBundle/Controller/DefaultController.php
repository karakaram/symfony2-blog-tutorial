<?php

namespace My\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use My\BlogBundle\Form\PostType;
use My\BlogBundle\Entity\Post;


class DefaultController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $posts = $em->getRepository('MyBlogBundle:Post')->findAll();
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
        if (!$form->isValid()) {
            return $this->render('MyBlogBundle:Default:new.html.twig', array(
                'form' => $form->createView(),
            ));
        }
        // エンティティを永続化
        $post = $form->getData();
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($post);
        $em->flush();
        $this->get('session')->setFlash('my_blog', '記事を追加しました');
        return $this->redirect($this->generateUrl('blog_index'));
    }

    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $post = $em->find('MyBlogBundle:Post', $id);
        return $this->render('MyBlogBundle:Default:show.html.twig', array('post' => $post));
    }

    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $post = $em->find('MyBlogBundle:Post', $id);
        if (!$post) {
            throw new NotFoundHttpException('The post does not exist.');
        }
        $em->remove($post);
        $em->flush();
        $this->get('session')->setFlash('my_blog', '記事を削除しました');
        return $this->redirect($this->generateUrl('blog_index'));
    }

    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $post = $em->find('MyBlogBundle:Post', $id);
        if (!$post) {
            throw new NotFoundHttpException('The post does not exist.');
        }
        $form = $this->createForm(new PostType(), $post);
        return $this->render('MyBlogBundle:Default:edit.html.twig', array(
            'post' => $post,
            'form' => $form->createView(),
        ));
    }

    public function editPostAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $post = $em->find('MyBlogBundle:Post', $id);
        if (!$post) {
            throw new NotFoundHttpException('The post does not exist.');
        }
        $form = $this->createForm(new PostType(), $post);

        // バリデーション
        $request = $this->getRequest();
        $form->bindRequest($request);
        if (!$form->isValid()) {
            return $this->render('MyBlogBundle:Default:edit.html.twig', array(
                'post' => $post,
                'form' => $form->createView(),
            ));
        }
        // 更新されたエンティティをデータベースに保存
        $post = $form->getData();
        $em->flush();
        $this->get('session')->setFlash('my_blog', '記事を編集しました');
        return $this->redirect($this->generateUrl('blog_index'));

    }

}
