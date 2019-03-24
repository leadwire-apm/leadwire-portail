<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Service\AuthService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends Controller
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
        if (method_exists($this, $provider . 'Action') === true) {
            return $this->{$provider . 'Action'}($request, $authService);
        } else {
            return new JsonResponse("Provider not found", 404);
        }
    }

    public function githubAction(Request $request, AuthService $authService)
    {
        $data = json_decode($request->getContent(), true);
        $parameters = $this->getParameter("auth_providers")['github'];
        $userData = $authService->githubProvider(
            [
                'client_id' => $data['clientId'],
                'redirect_uri' => $data['redirectUri'],
                'client_secret' => $parameters["github_client_secret"],
                'code' => $data['code'],
            ],
            $parameters["github_access_token_url"],
            $parameters["github_users_api_url"]
        );

        return new JsonResponse(
            [
                "token" => $authService->generateToken($userData),
            ]
        );
    }
}
