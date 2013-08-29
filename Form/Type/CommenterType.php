<?php

namespace Newscoop\CommentsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CommenterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // $builder->add('name', 'text');
        // $builder->add('email', 'email');
        // $builder->add('url', 'url');
    }

    public function getName()
    {
        return 'commenterForm';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Newscoop\Entity\Comment\Commenter',
        ));
    }
}