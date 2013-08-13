<?php

namespace Newscoop\CommentsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/testcomments")
     */
    public function indexAction(Request $request)
    {
        return $this->render('NewscoopCommentsBundle:Default:comment.html.twig');
        // return array();
    }

    /**
     * @Route("/admin/comments_plugin")
     */
    public function adminAction(Request $request)
    {
        return $this->render('NewscoopCommentsBundle:Default:admin.html.twig');
    }
}
