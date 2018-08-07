<?php declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Manager\UserManager;
use Doctrine\Bundle\MongoDBBundle\Logger\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
     * @param  UserManager $um
     * @param $email
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function verifAction(UserManager $um, $email)
    {
        $user = $um->getOneBy(['email' => $email]);
        if ($user->getIsEmailValid()) {
            return $this->redirect('/');
        }
        $user->setActive(true);
        $user->setIsEmailValid(true);
        $um->update($user);
        return $this->redirect('/');
    }
}
