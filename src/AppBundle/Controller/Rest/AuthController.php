<?php declare(strict_types=1);

namespace AppBundle\Controller\Rest;

use AppBundle\Service\AuthService;
use ATS\CoreBundle\Controller\Rest\BaseRestController;
use Firebase\JWT\JWT;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use ATS\CoreBundle\Service\Voter\AclVoter;
use ATS\CoreBundle\HTTPFoundation\CsvResponse;
use ATS\CoreBundle\Service\Exporter\Exporter;
use Symfony\Component\HttpFoundation\Session\Session;

class AuthController extends BaseRestController
{
    /**
     * @Route("/{provider}", methods="POST")
     *
     * @param Request $request
     * @param string $provider
     *
     * @return Response
     */
    public function getAuthAction(Request $request, AuthService $authService, $provider)
    {
        if (method_exists($this, $provider.'Action')) {
            return $this->{$provider.'Action'}($request, $authService);
        } else {
            return new JsonResponse("Provider not found", 404);
        }
    }


    public function githubAction(Request $request, AuthService $authService)
    {
        $data = json_decode($request->getContent(), true);

        $params = [
            'client_id' =>  $data ['clientId'],
            'redirect_uri' => $data['redirectUri'],
            'client_secret'=> $this->getParameter("github_client_secret"),
            'code'=>  $data['code'],
        ];
        $userData = $authService->githubProvider($params, $this->getParameter("github_access_token_url"), $this->getParameter("github_users_api_url"));
        $userData['timeout'] = time() + 1800;

        return new JsonResponse(["token" => $authService->generateToken($userData['_id'], $this->getParameter('token_secret'))]);
    }

    public function isNotPublic()
    {
        return false;
    }
}
