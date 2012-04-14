<?php

namespace My\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
        // フォームのビルド
        $form = $this->createFormBuilder(new Post())  // ここでPostクラスを使うため、ファイルの先頭あたりにuseを追加していることに注意
            ->add('title')
            ->add('body')
            ->getForm();

        // バリデーション
        $request = $this->getRequest();
        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);
            if ($form->isValid()) {
                // エンティティを永続化
                $post = $form->getData();
                $post->setCreatedAt(new \DateTime());
                $post->setUpdatedAt(new \DateTime());
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($post);
                $em->flush();
                return $this->redirect($this->generateUrl('blog_index'));
            }
        }

        // 描画
        return $this->render('MyBlogBundle:Default:new.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $post = $em->find('MyBlogBundle:Post', $id);
        return $this->render('MyBlogBundle:Default:show.html.twig', array('post' => $post));
    }
}
