<?php

namespace Newscoop\CommentsBundle\EventListener;

use Newscoop\Entity\Publication;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

// define('ACTION_SUBMIT_COMMENT_ERR_INTERNAL', 'action_comment_submit_err_internal');
// define('ACTION_SUBMIT_COMMENT_ERR_NO_SUBJECT', 'action_comment_submit_err_no_subject');
// define('ACTION_SUBMIT_COMMENT_ERR_NO_CONTENT', 'action_comment_submit_err_no_content');
// define('ACTION_SUBMIT_COMMENT_ERR_NO_ARTICLE', 'action_comment_submit_err_no_article');
// define('ACTION_SUBMIT_COMMENT_ERR_NOT_ENABLED', 'action_comment_submit_err_not_enabled');
// define('ACTION_SUBMIT_COMMENT_ERR_NO_EMAIL', 'action_comment_submit_err_no_email');
// define('ACTION_SUBMIT_COMMENT_ERR_NO_PUBLIC', 'action_comment_submit_err_no_public');
// define('ACTION_SUBMIT_COMMENT_ERR_NO_CAPTCHA_CODE', 'action_comment_submit_err_no_captcha_code');
// define('ACTION_SUBMIT_COMMENT_ERR_INVALID_CAPTCHA_CODE', 'action_comment_submit_err_invalid_captcha_code');
// define('ACTION_SUBMIT_COMMENT_ERR_BANNED', 'action_comment_submit_err_banned');
// define('ACTION_SUBMIT_COMMENT_ERR_REJECTED', 'action_comment_submit_err_rejected');

// require_once($GLOBALS['g_campsiteDir'].'/include/captcha/php-captcha.inc.php');

class CommentListener
{
    private $log;
    private $em;
    private $input;
    private $captchaEnabled;
    private $article;
    private $publication;

    public function __construct($logger, $em)
    {
        $this->log = $logger;
        $this->log->info('Comment!');
        $this->em = $em;
    }

    public function onCommentSubmit(GetResponseEvent $event)
    {
        // var_dump($event);
        // die('listener, sup');
        $this->log->info('onCommentSubmit got fired!');
        $request = $event->getRequest();
        // var_dump($formService->handleRequest($request));

        if ($request->getMethod() == 'POST') {

            // get the field names
            $commentForm = $request->get('commentForm');
            if (is_array($commentForm)) {
                // var_dump($commentForm);
                $commentFormFields = array();
                foreach ($commentForm as $key => $value) {
                    if ($key == 'commenter') {
                        foreach ($commentForm[$key] as $commenterKey => $commenterValue) {
                            $commentFormFields[] = $commenterKey;
                        }
                    } else {
                        if ($key != '_token') {
                            $commentFormFields[] = $key;
                        }
                    }
                }

                $formService = \Zend_Registry::get('container')->getService('newscoop_comments.form.service');
                $formService->config($commentFormFields, $request);
                $form = $formService->getFormObject();
                var_dump($form->isValid());

                if ($form->isValid()) {
                    var_dump('Form is valid!');
                } else {
                    var_dump($form->getErrors());
                    var_dump('Form is invalid!');
                }


                // var_dump($request);
                $parameters = $request->request;
                var_dump($parameters);

                $publicationId = $request->get('_newscoop_publication_metadata')['alias']['publication_id'];
                var_dump($publicationId);
                // $this->publication = new Publication($publicationId);
                $this->captchaEnabled = $this->em->getRepository('Newscoop\Entity\Publication')
                                    ->findOneById($publicationId)->getCaptchaEnabled();
                // var_dump($this->publication);
                var_dump($this->captchaEnabled);
                $articleID = $request->get('_newscoop_article_metadata')['id'];
            }

        }
    }

    private static function ifEmptyReturnString($input) {
        return (!empty($input) && !is_null($input)) ? $input : '';
    }

    private function processCaptcha()
    {
        if (!$this->captchaEnabled) {
            //if the Captcha is not enabled always return true
            return true;
        }
        return true;
    }
}