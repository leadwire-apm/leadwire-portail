<?php declare(strict_types=1);

namespace AppBundle\Controller\Rest;

use AppBundle\Service\AuthService;
use AppBundle\Service\UserService;
use ATS\CoreBundle\Controller\Rest\BaseRestController;
use FOS\RestBundle\Controller\Annotations\Route;
use SensioLabs\Security\Exception\HttpException;
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
     * @throws \HttpException
     */
    public function getMeAction(Request $request, AuthService $auth)
    {
        $user = $auth->getUserFromToken($request->headers->get('Authorization'));
        if (!$user) {
            throw new HttpException("Non Authorized", 401);
        }
        return $this->prepareJsonResponse([
            "avatar" => $user->getAvatar(),
            "login" => $user->getLogin(),
            "email" => $user->getEmail(),
            "id" => $user->getId(),
            "uuid" => $user->getUuid(),
            "fname" => $user->getUsername(),
            "contact" => $user->getContact(),
            "contactPreference" => $user->getContactPreference(),
            "acceptNewsLetter" => $user->getAcceptNewsLetter(),
            "company" => $user->getCompany(),
        ]);
    }


    /**
     * @Route("/{id}/update", methods="PUT")
     *
     * @param Request $request
     * @param UserService $userService
     *
     * @param $id
     * @return Response
     */
    public function updateUserAction(Request $request, UserService $userService, $id)
    {
        $data = $request->getContent();
        $successful = $userService->updateUser($data, $id);

        return $this->prepareJsonResponse($successful);
    }

    /**
     * @Route("/decode", methods="GET")
     *
     * @param Request $request
     * @param AuthService $auth
     */
    public function decodeAction(Request $request, AuthService $auth)
    {
        // @todo: remove this before closing branch.
        sd($auth->decodeToken($request->query->get('token')));
    }
}
