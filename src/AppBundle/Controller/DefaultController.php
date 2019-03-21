<?php declare (strict_types = 1);

namespace AppBundle\Controller;

use AppBundle\Manager\UserManager;
use AppBundle\Service\InvitationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DefaultController extends Controller
{
    /**
     * @Route("/verify/{email}", methods="GET", name="verify_email")
     *
     * @param  UserManager $um
     * @param string $email
     *
     * @return RedirectResponse
     */
    public function verifyEmailAction(UserManager $um, $email)
    {
        $user = $um->getOneBy(['email' => $email]);

        if ($user->isEmailValid() === true) {
            return $this->redirect('/');
        }

        $user->setActive(true);
        $user->setEmailValid(true);
        $um->update($user);

        return $this->redirect('/');
    }
}
