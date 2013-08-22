<?php

namespace Newscoop\CommentsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // var_dump($builder);
        // var_dump($options);
        $builder->add('commenter', new CommenterType());
        $builder->add('subject', 'text');
        $builder->add('message', 'textarea');
        $builder->add("email_protect", "text", array(
        "mapped" => false,
        "constraints" => Blank,
        "required" => false,
        "max_length" => 20));
        $builder->add('save', 'submit');
    }

    public function getName()
    {
        return 'commentForm';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Newscoop\Entity\Comment',
        ));
    }
}