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
                // get the fieldnames
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
                    $comment = $form->getData();

                    $threadLevel = 0;
                    $threadOrder = 0;

                    if (is_null($comment->getCommenter()->getUser()) || is_null($comment->getCommenter()->getId())) {
                        $commenterRepository = $this->em->getRepository('Newscoop\Entity\Comment\Commenter');

                        $commenter = $commenterRepository->findOneBy(array(
                                'name' =>$comment->getCommenter()->getName(),
                                'email' =>$comment->getCommenter()->getEmail(),
                                'ip'    =>$comment->getCommenter()->getIp()
                        ));

                        if (!is_null($commenter)) {
                            $comment->setCommenter($commenter);
                        } else {
                            $commenter = $commenterRepository->findOneBy(array(
                                    'email' =>$comment->getCommenter()->getEmail(),
                                    'ip'    =>$comment->getCommenter()->getIp()
                                    ));
                            if (!is_null($commenter)) {
                                $comment->setCommenter($commenter);
                            }
                        }
                    }

                    if (array_key_exists('commentparent', $request->get('commentForm'))) {
                        if (!empty($request->get('commentForm')['commentparent'])) {
                            $parent = $this->em->getRepository('Newscoop\Entity\Comment')->findOneById(intval($request->get('commentForm')['commentparent']));
                            // var_dump($parent);
                            if ($parent instanceof Newscoop\Entity\Comment) {
                                var_dump($parent);
                                $comment->setParent($parent);
                                $qb = $this->em->getRepository('Newscoop\Entity\Comment')->createQueryBuilder('c');

                                // get the maximum thread order from the current parent
                                $threadOrder =   $qb->select('MAX(c.thread_order)')
                                                    ->andwhere('c.parent = :parent')
                                                    ->andWhere('c.thread = :thread')
                                                    ->andWhere('c.language = :language')
                                                    ->setParameter('parent', $parent)
                                                    ->setParameter('thread', $parent->getThread()->getId())
                                                    ->setParameter('language', $parent->getLanguage()->getId())
                                                    ->getQuery()
                                                    ->getSingleScalarResult();

                                // if the comment parent doesn't have children then use the parent thread order
                                if (empty($threadOrder)) {
                                    $threadOrder = $parent->getThreadOrder();
                                }

                                $threadOrder += 1;

                                /**
                                * update all the comment for the thread where thread order is less or equal
                                * of the current thread_order
                                */
                                // $qb->update()
                                //     ->set('c.thread_order',  'c.thread_order+1')
                                //     ->andwhere('c.thread_order >= :thread_order')
                                //     ->andWhere('c.thread = :thread')
                                //     ->andWhere('c.language = :language')
                                //     ->setParameter('language', $parent->getLanguage()->getId())
                                //     ->setParameter('thread', $parent->getThread()->getId())
                                //     ->setParameter('thread_order', $threadOrder);
                                // $qb->getQuery()->execute();

                                // // set the thread level the thread level of the parent plus one the current level
                                $threadLevel = $parent->getThreadLevel();
                                $threadLevel += 1;
                                
                                var_dump('threadLevel: '.$threadLevel);
                                var_dump('threadOrder: '.$threadOrder);
                            }
                        }
                    } else {
                        $qb = $this->em->getRepository('Newscoop\Entity\Comment')->createQueryBuilder('c');
                        $threadOrder = $qb->select('MAX(c.thread_order)')
                            ->where('c.thread = :thread')
                            ->andWhere('c.language = :language')
                            ->setParameter('thread', $thread->getNumber())
                            ->setParameter('language', $language->getId())
                            ->getQuery()
                            ->getSingleScalarResult();

                        // increase by one of the current comment
                        // $threadOrder = $query->getSingleScalarResult() + 1;
                        var_dump($threadOrder);
                        $threadOrder += 1;
                    }

                    $comment->setThreadLevel($threadLevel);
                    $comment->setThreadOrder($threadOrder);

                    $publicationObj = $comment->getForum();
                    // var_dump($publicationObj);

                    $user = null;
                    if ($comment->getCommenter() instanceof Newscoop\Entity\Commenter) {
                        $user = $comment->getCommenter();
                    }
                    // var_dump($user);

                    if ((!is_null($user) && $publicationObj->getCommentsSubscribersModerated())
                    || (is_null($user) && $publicationObj->getCommentsPublicModerated())) {
                        $comment->setStatus('pending');
                    } else {
                        $comment->setStatus('approved');
                    }
                    

                    // var_dump($comment);

                    $this->em->persist($comment);
                    $this->em->flush();
                } else {
                    var_dump($form->getErrors());
                    // $comment = $form->getData();
                    // var_dump($comment);
                    var_dump('Form is invalid!');
                }


                // var_dump($request);
                $parameters = $request->request;
                var_dump($parameters);
            }

        }
    }
}