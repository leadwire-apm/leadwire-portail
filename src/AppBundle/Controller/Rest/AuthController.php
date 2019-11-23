<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Service\AuthService;
use AppBundle\Service\ProcessService;
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
    public function getAuthAction(Request $request, AuthService $authService, ProcessService $processService, $provider)
    {
        if (method_exists($this, $provider . 'Action') === true) {
            $processService->emit("heavy-operations-in-progress", "Processing login");
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

    public function loginAction(Request $request, AuthService $authService)
    {
        $data = json_decode($request->getContent(), true);
        $params = [
            'username' => $data['username'],
        ];

        $userData = $authService->loginProvider($params);

        return new JsonResponse(
            [
                "token" => $authService->generateToken($userData),
            ]
        );
    }

    public function proxyAction(Request $request, AuthService $authService)
    {
        if ($request->headers->get('username') === null ||
            $request->headers->get('email') === null ||
            $request->headers->get('group') === null
        ) {
            return new JsonResponse("Headers not found", 404);
        }

        $params = [
            'username' => $request->headers->get('username'),
            'email' => $request->headers->get('email'),
            'group' => $request->headers->get('group'),
        ];

        $userData = $authService->proxyLoginProvider($params);

        return new JsonResponse(
            [
                "token" => $authService->generateToken($userData),
            ]
        );
    }
}
