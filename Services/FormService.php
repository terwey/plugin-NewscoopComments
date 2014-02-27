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
    private $hasUser;

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

    private $captchaEnabled;
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
        $this->securityContext = $securityContext;
        $this->formFactory = $formFactory;
        $this->em = $em;
    }

    public function config(array $parameters = array(), Request $request = null)
    {
        $this->request = $request;

        $zendAuth = \Zend_Auth::getInstance();
        $user = null;
        if ($zendAuth->hasIdentity()) {
            $userId = $zendAuth->getIdentity();

            $userRepository = $this->em->getRepository('Newscoop\Entity\User');
            $user = $userRepository->findOneBy(array('id'=>$userId));
        }

        // var_dump($request);
        $publicationMetaData = $request->get('_newscoop_publication_metadata');
        $publicationId = $publicationMetaData['alias']['publication_id'];
        $publication = $this->em->getRepository('Newscoop\Entity\Publication')->findOneBy(array('id'=>$publicationId));
        $articleMetadata = $request->get('_newscoop_article_metadata');

        $comment = new Comment();
        $comment->setForum($this->em->getRepository('Newscoop\Entity\Publication')->findOneBy(array('id'=>$publicationId)));
        $comment->setThread($this->em->getRepository('Newscoop\Entity\Article')->getArticle($articleMetadata['id'], $articleMetadata['language_id'])->getOneOrNullResult());
        $comment->setIp($this->request->getClientIp());
        $comment->setLanguage($this->em->getRepository('Newscoop\Entity\Language')->findOneBy(array('id'=>$articleMetadata['language_id'])));

        if (!is_null($user)) {
            $commenterRepository = $this->em->getRepository('Newscoop\Entity\Comment\Commenter');
            $commenter = $commenterRepository->findOneBy(array('user'=>$user));
            if (is_null($commenter)) {
                $commenter = new Commenter();
                $commenter->setName($user->getName());
                $commenter->setEmail($user->getEmail());
                
                $commenter->setUser($user);
                // $commenter->setUrl('');
            }
            $this->hasUser = true;
        } else {
            $commenter = new Commenter();
            $this->hasUser = false;
        }

        
        $commenter->setIp($this->request->getClientIp());
        $comment->setCommenter($commenter);

        $this->form = $this->formFactory->create(new CommentType(), $comment);

        if (in_array('commentparent', $parameters)) {
            $this->form->add("commentparent", "hidden", array(
                    "mapped" => false,
                    "required" => false,
            ));
        }

        if (in_array('subject', $parameters)) {
            $this->form->add('subject', 'text');
        }

        if (in_array('content', $parameters) || in_array('message', $parameters)) {
            $this->form->add('message', 'textarea', array(
                "required" => true
            ));
        }

        if (in_array('name', $parameters) || in_array('email', $parameters) ||  in_array('url', $parameters)) {
            $this->form->add('commenter', new CommenterType());
            
            if (!$this->hasUser) {
                if (in_array('name', $parameters)) {
                    $this->form->get('commenter')->add('name', 'text', array(
                        "required" => true
                    ));
                }
                if (in_array('email', $parameters)) {
                    $this->form->get('commenter')->add('email', 'email', array(
                        "required" => true
                    ));
                }
                if (in_array('url', $parameters)) {
                    $this->form->get('commenter')->add('url', 'url');
                }
            }
        }

        if (in_array('spam_protect', $parameters) || in_array('email_protect', $parameters)) {
            $this->form->add("email_protect", "text", array(
                    "mapped" => false,
                    "constraints" => array(
                        new \Symfony\Component\Validator\Constraints\Blank()
                    ),
                    "required" => false,
                    "max_length" => 20
            ));
        }
        $this->captchaEnabled = $this->em->getRepository('Newscoop\Entity\Publication')->findOneById($publicationId)->getCaptchaEnabled();
        if ($this->captchaEnabled) {
            if (in_array('recaptcha', $parameters) || in_array('captcha', $parameters)) {
                $this->form->add('recaptcha', 'ewz_recaptcha', array(
                        "mapped" => false,
                        'constraints'   => array(
                            new \EWZ\Bundle\RecaptchaBundle\Validator\Constraints\True()
                        )
                ));
            }
        }

        $this->formHelper = \Zend_Registry::get('container')->getService('templating.helper.form');

        if ($this->request->getMethod() == 'POST') {
            $this->form->handleRequest($this->request);
        }

        $this->formView = $this->form->createView();
    }

    public function getElement($elementName, array $options = array())
    {
        if (empty($this->$elementName)) {
            $elementName = ($elementName == 'content') ? 'message' : $elementName;
            $elementName = ($elementName == 'spam_protect') ? 'email_protect' : $elementName;
            $elementName = ($elementName == 'captcha') ? 'recaptcha' : $elementName;
            // $elementName = ($elementName == 'parent') ? 'commentparent' : $elementName;
            
            $allowedElementsGeneral = array('message', 'subject', 'save', 'email_protect', 'recaptcha', 'commentparent');
            $allowedElementsCommenter = array('name', 'email', 'url');
            $outputType = (!empty($options['label'])) ? 'row' : 'widget';
            if (in_array($elementName, $allowedElementsGeneral)  || in_array($elementName, $allowedElementsCommenter)) {
                if (empty($this->$elementName)) {
                    if (in_array($elementName, $allowedElementsCommenter)) {
                        if (!$this->hasUser) {
                            if (!empty($options)) {
                                $this->$elementName = $this->formHelper->$outputType($this->formView['commenter'][$elementName], $options);
                            } else {
                                $this->$elementName = $this->formHelper->$outputType($this->formView['commenter'][$elementName]);
                            }
                        } else {
                            $this->$elementName = '<!-- User is logged in -->';
                        }
                    } else {
                        if (!$this->captchaEnabled && $elementName == 'recaptcha') {
                            $this->$elementName = '<!-- ReCaptcha disabled in the Publication -->';
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

    public function getData()
    {
        return $this->form->getData();
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