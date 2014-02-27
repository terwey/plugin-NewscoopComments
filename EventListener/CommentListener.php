<?php

namespace Newscoop\CommentsBundle\EventListener;

use Newscoop\Entity\Publication;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

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
        $this->log->info('onCommentSubmit got fired!');
        $request = $event->getRequest();

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

                if ($form->isValid()) {
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

                    if (array_key_exists('commentparent', $commentForm) && $commentForm['commentparent'] != '') {
                        $parent = $this->em->getRepository('Newscoop\Entity\Comment')->findOneById(intval($commentForm['commentparent']));
                        if ($parent instanceof \Newscoop\Entity\Comment) {
                            $comment->setParent($parent);
                            
                            $qb = $this->em->getRepository('Newscoop\Entity\Comment')->createQueryBuilder('c');

                            // get the maximum thread order from the current parent
                            $threadOrder =   $qb->select('MAX(c.thread_order)')
                                                ->andwhere('c.parent = :parent')
                                                ->andWhere('c.thread = :thread')
                                                ->andWhere('c.language = :language')
                                                ->setParameter('parent', $parent)
                                                ->setParameter('thread', $comment->getThread()->getId())
                                                ->setParameter('language', $comment->getLanguage()->getId())
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
                            $qb = $this->em->getRepository('Newscoop\Entity\Comment')->createQueryBuilder('c');
                            $qb->update()
                                ->set('c.thread_order',  'c.thread_order+1')
                                ->andwhere('c.thread_order >= :thread_order')
                                ->andWhere('c.thread = :thread')
                                ->andWhere('c.language = :language')
                                ->setParameter('language', $comment->getLanguage()->getId())
                                ->setParameter('thread', $comment->getThread()->getId())
                                ->setParameter('thread_order', $threadOrder);
                            $qb->getQuery()->execute();

                            // set the thread level the thread level of the parent plus one the current level
                            $threadLevel = $parent->getThreadLevel();
                            $threadLevel += 1;
                        }
                    } else {
                        $qb = $this->em->getRepository('Newscoop\Entity\Comment')->createQueryBuilder('c');
                        $threadOrder = $qb->select('MAX(c.thread_order)')
                            ->where('c.thread = :thread')
                            ->andWhere('c.language = :language')
                            ->setParameter('thread', $comment->getThread()->getId())
                            ->setParameter('language', $comment->getLanguage()->getId())
                            ->getQuery()
                            ->getSingleScalarResult();

                        // increase by one of the current comment
                        $threadOrder += 1;
                    }

                    $comment->setThreadLevel($threadLevel);
                    $comment->setThreadOrder($threadOrder);

                    $publicationObj = $comment->getForum();

                    $user = null;
                    if ($comment->getCommenter() instanceof \Newscoop\Entity\Commenter) {
                        $user = $comment->getCommenter();
                    }

                    if ((!is_null($user) && $publicationObj->getCommentsSubscribersModerated())
                    || (is_null($user) && $publicationObj->getCommentsPublicModerated())) {
                        $comment->setStatus('pending');
                    } else {
                        $comment->setStatus('approved');
                    }
                    


                    $this->em->persist($comment);
                    $this->em->flush();
                } else {
                    var_dump($form->getErrors());
                    var_dump('Form is invalid!');
                }


                $parameters = $request->request;
            }

        }
    }
}