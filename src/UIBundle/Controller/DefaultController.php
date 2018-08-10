<?php

namespace UIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="angular_endPoint")
     */
    public function indexAction()
    {
        return $this->render('UIBundle:Default:index.html.twig', []);
    }
}
