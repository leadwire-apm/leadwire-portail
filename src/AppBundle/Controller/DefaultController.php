<?php declare (strict_types = 1);

namespace AppBundle\Controller;

use AppBundle\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

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
