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
	private $services;

    private $em;
    private $formBuilder;
    private $comment;
    private $formFactory;
    // private $form;
    private $formView;
    private $formHelper;

    private $subject;
    private $message;
    private $name;
    private $email;
    private $url;

    private $formStart;
    private $formEnd;

    // public function get($name)
    // {
    // 	if (array_key_exists($name, $this->services)) {
    // 		return $this->services[$name];
    // 	} else {
    // 		return $this->init($name);
    // 	}
    // }

    // private function init($name)
    // {

    // }
    
    public function __construct(EntityManager $em)
    {
        var_dump('constructer!');
        var_dump(md5(spl_object_hash($this)));
    }

    public function config(array $parameters = array())
    {
        if (!($this->formFactory instanceof Symfony\Component\Form\FormFactory)) {
            $this->formFactory = \Zend_Registry::get('container')->getService('form.factory');
            // var_dump(get_class($this->formFactory));
        }
        if (!($comment instanceof Newscoop\Entity\Comment)) {
            $comment = new Comment();
            $comment->setMessage('foobar');
            // var_dump(get_class($comment));
        }
        if (!($form instanceof Symfony\Component\Form\Form)) {
            $form = $this->formFactory->create(new CommentType(), $comment);
            var_dump(get_class($form));
        }
        if (!($this->formHelper instanceof Symfony\Bundle\FrameworkBundle\Templating\Helper\FormHelper)) {
            $this->formHelper = \Zend_Registry::get('container')->getService('templating.helper.form');
            // var_dump(get_class($this->formHelper));
        }

        // $html = $this->formHelper->form($form->createView());

        if (!($this->formView instanceof Symfony\Component\Form\FormView)) {
            $this->formView = $form->createView();
            // var_dump(get_class($this->formView));
        }
    }

    public function getElement($elementName, array $options = array())
    {
        if (empty($this->$elementName)) {
            $elementName = ($elementName == 'content') ? 'message' : $elementName;
            $elementName = ($elementName == 'spam_protect') ? 'email_protect' : $elementName;
            $allowedElementsGeneral = array('message', 'subject', 'save', 'email_protect');
            $allowedElementsCommenter = array('name', 'email', 'url');
            $outputType = (!empty($options['label'])) ? 'row' : 'widget';
            // $outputType = ($elementName == 'email_protect') ? 'row' : $outputType;
            if (in_array($elementName, $allowedElementsGeneral)  || in_array($elementName, $allowedElementsCommenter)) {
                if (empty($this->$elementName)) {
                    if (in_array($elementName, $allowedElementsCommenter)) {
                        if (!empty($options)) {
                            $this->$elementName = $this->formHelper->$outputType($this->formView['commenter'][$elementName], $options);
                        } else {
                            $this->$elementName = $this->formHelper->$outputType($this->formView['commenter'][$elementName]);
                        }
                    } else {
                        if (!empty($options)) {
                            $this->$elementName = $this->formHelper->$outputType($this->formView[$elementName], $options);
                        } else {
                            $this->$elementName = $this->formHelper->$outputType($this->formView[$elementName]);
                        }
                    }
                }
            }
        }
        return $this->$elementName;
    }

    public function getFormStart()
    {
        if (empty($this->formStart)) {
            $this->formStart = $this->formHelper->start($this->formView);
        }
        return $this->formStart; 
    }

    public function getFormEnd()
    {
        if (empty($this->formEnd)) {
            $this->formEnd = $this->formHelper->end($this->formView);
        }
        return $this->formEnd; 
    }
}