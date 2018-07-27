<?php

namespace UIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        //@todo fix this hack
        return new Response(file_get_contents(__DIR__ . '/../Resources/public/index.html'));
        // return $this->render('@UIBundle/Resources/public/index.html');
    }
}
