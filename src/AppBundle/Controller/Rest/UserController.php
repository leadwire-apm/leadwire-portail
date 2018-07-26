<?php declare(strict_types=1);

namespace AppBundle\Controller\Rest;

use AppBundle\Service\AuthService;
use AppBundle\Service\UserService;
use ATS\CoreBundle\Controller\Rest\BaseRestController;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class UserController extends BaseRestController
{

    /**
     * @Route("/me", methods="GET")
     *
     * @param Request $request
     * @param AuthService $auth
     * @return Response
     */
    public function getMeAction(Request $request, AuthService $auth, UserService $userService)
    {
        $jwt = explode(' ', $request->headers->get('Authorization'));
        $token = $auth->decodeToken($jwt[1]);

        return $this->prepareJsonResponse($userService->getUser(
            $token->user
        ));
    }

    public function isNotPublic()
    {
        return true;
    }
}
