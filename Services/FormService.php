<?php

namespace Newscoop\CommentsBundle\Services;

use Newscoop\Form\FormServiceInterface;
use Newscoop\Entity\Comment;
use Newscoop\Entity\Comment\Commenter;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityManager;
use Newscoop\CommentsBundle\Form\Type\CommentType;
use Newscoop\CommentsBundle\Form\Type\CommenterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Form\FormFactory;


// use Symfony\Component\Validator\Constraints\Length;
// use Symfony\Component\Validator\Constraints\Blank;

/**
 * FormService
 */
class FormService implements FormServiceInterface
{
	private $services;

    private $securityContext;
    private $em;

    private $formBuilder;
    private $comment;
    private $formFactory;
    private $form;
    private $commenterForm;
    private $formView;
    private $formHelper;

    private $subject;
    private $message;
    private $name;
    private $email;
    private $url;

    private $recaptcha;

    private $formStart;
    private $formEnd;

    // protected $request;

    // public function setRequest(Request $request = null)
    // {
    //     $this->request = $request;
    // }
    
    public function __construct(EntityManager $em, SecurityContext $securityContext, FormFactory $formFactory)
    {
        // var_dump('constructer!');
        // var_dump(md5(spl_object_hash($this)));
        $this->securityContext = $securityContext;
        // var_dump($this->securityContext);
        $this->formFactory = $formFactory;
        $this->em = $em;
    }

    public function config(array $parameters = array(), Request $request = null)
    {
        $this->request = $request;
        $zendAuth = \Zend_Auth::getInstance();
        if ($zendAuth->hasIdentity()) {
            $userId = $zendAuth->getIdentity();

            $userRepository = $this->em->getRepository('Newscoop\Entity\User');
            $user = $userRepository->findOneBy(array('id'=>$userId));
        }

        // $token = $this->securityContext->getToken();
        // $user = $token->getUser();



        $comment = new Comment();
        // if (!is_null($this->request)) {
        //     $comment->setIp($this->request->getClientIp()); // set the IP
        // }
        // if (!is_null($user)) {
        //     $commenterRepository = $this->em->getRepository('Newscoop\Entity\Comment\Commenter');
        //     $commenter = $commenterRepository->findOneBy(array('user'=>$user));
        //     if (is_null($commenter)) {
        //         $commenter = new Commenter();
        //         $commenter->setName($user->getName());
        //         $commenter->setEmail($user->getEmail());
        //         if (!is_null($this->request)) {
        //             $commenter->setIp($this->request->getClientIp());
        //         }
        //         $commenter->setUser($user);
        //     }
        //     $comment->setCommenter($commenter);
        // }



        $this->form = $this->formFactory->create(new CommentType(), $comment);


        if (in_array('subject', $parameters)) {
            $this->form->add('subject', 'text');
        }

        if (in_array('content', $parameters) || in_array('message', $parameters)) {
            $this->form->add('message', 'textarea');
        }

        if (in_array('name', $parameters) || in_array('email', $parameters) ||  in_array('url', $parameters)) {
            $this->form->add('commenter', new CommenterType());
            
            if (in_array('name', $parameters)) {
                $this->form->get('commenter')->add('name', 'text');
            }
            if (in_array('email', $parameters)) {
                $this->form->get('commenter')->add('email', 'email');
            }
            if (in_array('url', $parameters)) {
                $this->form->get('commenter')->add('url', 'url');
            }
        }


        if (in_array('spam_protect', $parameters) || in_array('email_protect', $parameters)) {
            $this->form->add(
                "email_protect",
                "text",
                array(
                    "mapped" => false,
                    "constraints" => array(
                        new \Symfony\Component\Validator\Constraints\Blank()
                    ),
                    "required" => false,
                    "max_length" => 20
                )
            );
        }

        if (in_array('recaptcha', $parameters) || in_array('captcha', $parameters)) {
            $this->form->add('recaptcha', 'ewz_recaptcha',
                array(
                    "mapped" => false,
                    'constraints'   => array(
                        new \EWZ\Bundle\RecaptchaBundle\Validator\Constraints\True()
                    )
                    ));
            // var_dump($this->form);
        }


        // if (!($this->formHelper instanceof Symfony\Bundle\FrameworkBundle\Templating\Helper\FormHelper)) {
            $this->formHelper = \Zend_Registry::get('container')->getService('templating.helper.form');
        // }

        if (!is_null($this->request)) {
            if ($this->request->getMethod() == 'POST') {
                $this->form->handleRequest($this->request);
            }
        }

        // if (!($this->formView instanceof Symfony\Component\Form\FormView)) {
            $this->formView = $this->form->createView();
        // }
    }

    public function getElement($elementName, array $options = array())
    {
        if (empty($this->$elementName)) {
            $elementName = ($elementName == 'content') ? 'message' : $elementName;
            $elementName = ($elementName == 'spam_protect') ? 'email_protect' : $elementName;
            $elementName = ($elementName == 'captcha') ? 'recaptcha' : $elementName;
            $allowedElementsGeneral = array('message', 'subject', 'save', 'email_protect', 'recaptcha');
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

    public function getFormObject()
    {
        return $this->form;
    }

    public function isValid()
    {
        return $this->form->isValid();
    }

    public function getErrors()
    {
        return $this->form->getErrors();
    }
}