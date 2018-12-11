<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Service\AuthService;
use ATS\CoreBundle\Controller\Rest\BaseRestController;
use FOS\RestBundle\Controller\Annotations\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends BaseRestController
{
    /**
     * @Route("/{provider}", methods={"POST", "PATCH"})
     * @param Request $request
     * @param string $provider
     *
     * @return Response
     */
    public function getAuthAction(Request $request, AuthService $authService, $provider)
    {
        if (method_exists($this, $provider . 'Action')) {
            return $this->{$provider . 'Action'}($request, $authService);
        } else {
            return new JsonResponse("Provider not found", 404);
        }
    }

    public function githubAction(Request $request, AuthService $authService)
    {
        $data = json_decode($request->getContent(), true);
        $parameters = $this->getParameter("auth_providers")['github'];
        $globalSettnigs = $this->getParameter('auth_providers')['settings'];
        $params = [
            'client_id' => $data['clientId'],
            'redirect_uri' => $data['redirectUri'],
            'client_secret' => $parameters["github_client_secret"],
            'code' => $data['code'],
        ];
        $userData = $authService->githubProvider(
            $params,
            $parameters["github_access_token_url"],
            $parameters["github_users_api_url"]
        );

        return new JsonResponse(
            [
                "token" => $authService->generateToken($userData, $globalSettnigs['token_secret']),
            ]
        );
    }
}
