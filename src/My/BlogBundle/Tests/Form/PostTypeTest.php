<?php

namespace My\BlogBundle\Form;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use My\BlogBundle\Form\PostType;
use My\BlogBundle\Entity\Post;

class PostTypeTest extends WebTestCase
{
    private $container;
    private $token;
    
    public function setUp()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $this->container = $kernel->getContainer();
        $this->token = $this->container->get('form.csrf_provider')->generateCsrfToken('unknown');
    }
    
    public function test正常系()
    {
        $form = $this->container->get('form.factory')->create(new PostType, new Post());
        $data['title'] = 'title';
        $data['body'] = 'bodybodybody';
        $data['_token'] = $this->token;
        $form->bind($data);
        $this->assertTrue($form->isValid());
    }

    public function test必須チェック()
    {
        $form = $this->container->get('form.factory')->create(new PostType, new Post());
        $data['title'] = '';
        $data['body'] = '';
        $data['_token'] = $this->token;
        $form->bind($data);
        $this->assertFalse($form->isValid());
        
        $childForms = $form->getChildren();
        $this->assertTrue($childForms['title']->hasErrors());
        $errorForms = $childForms['title']->getErrors();
        $errorMessage = $errorForms[0]->getMessageTemplate();
        $this->assertSame($errorMessage, 'This value should not be blank');
        $this->assertTrue($childForms['body']->hasErrors());
        $errorForms = $childForms['body']->getErrors();
        $errorMessage = $errorForms[0]->getMessageTemplate();
        $this->assertSame($errorMessage, 'This value should not be blank');
    }

    public function test最小文字数チェック()
    {
        $form = $this->container->get('form.factory')->create(new PostType, new Post());
        $data['title'] = '1';
        $data['body'] = '1';
        $data['_token'] = $this->token;
        $form->bind($data);
        $this->assertFalse($form->isValid());
        
        $childForms = $form->getChildren();
        $this->assertTrue($childForms['title']->hasErrors());
        $errorForms = $childForms['title']->getErrors();
        $errorMessage = $errorForms[0]->getMessageTemplate();
        $this->assertSame($errorMessage, 'This value is too short. It should have {{ limit }} characters or more');
        $this->assertTrue($childForms['body']->hasErrors());
        $errorForms = $childForms['body']->getErrors();
        $errorMessage = $errorForms[0]->getMessageTemplate();
        $this->assertSame($errorMessage, 'This value is too short. It should have {{ limit }} characters or more');
    }

    public function test最大文字数チェック()
    {
        $form = $this->container->get('form.factory')->create(new PostType, new Post());
        $longCharacter = '';
        for ($i=0; $i < 51; $i++) {
            $longCharacter .= 'a';
        }
        $data['title'] = $longCharacter;
        $data['body'] = $longCharacter;
        $data['_token'] = $this->token;
        $form->bind($data);
        $this->assertFalse($form->isValid());
        
        $childForms = $form->getChildren();
        $this->assertTrue($childForms['title']->hasErrors());
        $errorForms = $childForms['title']->getErrors();
        $errorMessage = $errorForms[0]->getMessageTemplate();
        $this->assertSame($errorMessage, 'This value is too long. It should have {{ limit }} characters or less');
        $this->assertFalse($childForms['body']->hasErrors());
    }

}
