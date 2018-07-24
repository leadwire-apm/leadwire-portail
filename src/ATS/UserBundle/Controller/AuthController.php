<?php

namespace ATS\UserBundle\Controller;

use ATS\CoreBundle\Controller\Rest\BaseRestController;
use ATS\UserBundle\Service\AuthService;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * AuthController
 *
 * @author Hounaida ZANNOUN <hzannoun@ats-digital.com>
 */
class AuthController extends BaseRestController
{

    /**
     * @Route("/register", methods="POST")
     *
     * @param Request $request
     * @param AuthService $authService
     *
     * @return Response
     */

    public function registerAction(Request $request, AuthService $authService)
    {
        $data = $request->getContent();

        return $this->prepareJsonResponse(
            $authService->register($data)
        );
    }

    /**
     * @Route("/login_check", methods="POST")
     *
     * @param Request $request
     * @param AuthService $authService
     *
     * @return Response
     */

    public function loginCheckAction(Request $request, AuthService $authService)
    {
        $authData = json_decode($request->getContent());

        $clientSecret = $authService->loginCheck($authData->username, $authData->client_id);

        if ($clientSecret) {
            return new RedirectResponse(
                $this->generateUrl(
                    'fos_oauth_server_token',
                    [
                        'client_id' => $authData->client_id,
                        'client_secret' => $clientSecret,
                        'grant_type' => 'password',
                        'username' => $authData->username,
                        'password' => $authData->password,
                    ]
                )
            );
        }

        throw new UnauthorizedHttpException('Basic realm=' . $this->getParameter('app_domain'));
    }

    /**
     * @Route("/logout", methods="POST")
     *
     * @param Request $request
     * @param AuthService $authService
     *
     * @return Response
     */

    public function logoffAction(Request $request, AuthService $authService)
    {
        $authHeader = $request->headers->get("Authorization");
        $logoutAttempt = $authService->logout($authHeader);

        return new JsonResponse($logoutAttempt);
    }

    /**
     * @Route("/refresh", methods="PUT")
     *
     * @param Request $request
     * @param AuthService $authService
     *
     * @return Response
     */

    public function refreshAccessTokenAction(Request $request, AuthService $authService)
    {
        $authHeader = $request->headers->get("Authorization");
        $refreshAttempt = $authService->refreshToken($authHeader);

        return new JsonResponse($refreshAttempt);
    }
}
