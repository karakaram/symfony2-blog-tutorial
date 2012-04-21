<?php

namespace My\BlogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class PostType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('title', 'text');
        $builder->add('body', 'text');
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'My\BlogBundle\Entity\Post',
        );
    }

    public function getName()
    {
        return 'post';
    }
}
