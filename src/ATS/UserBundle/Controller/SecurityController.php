<?php declare(strict_types=1);

namespace ATS\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Security controller
 *
 * @author Wajih WERIEMI <wweriemi@ats-digital.com>
 */
class SecurityController extends Controller
{
    /**
     * Login action
     *
     * @param Request             $request
     * @param AuthenticationUtils $authUtils
     *
     * @return Response
     */
    public function loginAction(Request $request, AuthenticationUtils $authUtils)
    {
        $error = $authUtils->getLastAuthenticationError();
        $lastUsername = $authUtils->getLastUsername();

        return $this->render('UserBundle:Security:login.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $error,
        ));
    }
}
