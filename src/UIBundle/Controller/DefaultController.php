<?php

namespace UIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="angular_endPoint", methods="GET")
     */
    public function indexAction()
    {
        return $this->render('@UI/Default/index.html.twig');
    }
}
