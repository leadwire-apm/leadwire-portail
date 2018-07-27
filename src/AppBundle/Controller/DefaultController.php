<?php declare(strict_types=1);

namespace AppBundle\Controller;

use Doctrine\Bundle\MongoDBBundle\Logger\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/logedin", methods="GET")
     */
    public function indexAction()
    {
        return $this->render('AppBundle:Default:index.html.twig');
    }

    /**
     * @Route("/verify/{email}", methods="GET", name="verify_email")
     */
    public function verifAction()
    {
        return $this->redirect('/');
    }
}
