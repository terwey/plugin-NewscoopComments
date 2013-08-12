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
        return $this->render('CommentsBundle:Default:index.html.smarty');
    }

    /**
     * @Route("/admin/comments_plugin")
     * @Template()
     */
    public function adminAction(Request $request)
    {
    	return array();
    }
}
