<?php declare(strict_types=1);

namespace AppBundle\Controller\Rest;


use AppBundle\Service\AuthService;
use ATS\CoreBundle\Controller\Rest\BaseRestController;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class UserController extends BaseRestController
{

    /**
     * @Route("/me", methods="GET")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getMeAction(Request $request)
    {
        //d(['foo' => 'bar']);
        $session = new Session();
        $session->start();
        $data = $session->get('userData');
        if ($data['timeout'] >= time())
        return new JsonResponse($data);
       /* return new JsonResponse([
            "avatar"=> "https://avatars0.githubusercontent.com/u/4384554?v=4",
            "displayName"=> "Anis Ksontini",
            "email"=> "leadwire-apm-test",
            "github"=> "4384554",
            "id"=> 9,
            "login"=>"ksontini",
            "fname"=>"Anis Ksontini",
            "password"=>"test-apm-leadwire",
        ]);*/
       else
           return new JsonResponse(['result' => "Not authenticated"], 401);
    }

    public function isNotPublic()
    {
        return true;
    }
}