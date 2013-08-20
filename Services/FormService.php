<?php

namespace Newscoop\CommentsBundle\Services;

use Newscoop\Entity\Comment;
use Newscoop\Entity\Comment\Commenter;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityManager;
use Newscoop\CommentsBundle\Form\Type\CommentType;

/**
 * FormService
 */
class FormService
{
    private $_em;
    
    public function __construct(EntityManager $em)
    {
        $this->_em = $em;
    }

    public function createForm($param)
    {
    	$formBuilder = new FormBuilder();
        $comment = new Comment();
        $comment->setMessage('foobar');
        // var_dump($comment);
        // var_dump($param);
        // var_dump('it works yo');
        $formFactory = \Zend_Registry::get('container')->getService('form.factory');
        // var_dump($formFactory);
        $form = $formFactory->create(new CommentType(), $comment);
        // var_dump($form);

        // var_dump($form->createView());
        $formHelper = \Zend_Registry::get('container')->getService('templating.helper.form');
        $html = $formHelper->form($form->createView());

        // var_dump($form);

        $formView = $form->createView();
        var_dump($formHelper->row($formView['commenter']));

        return $html;
    }
}