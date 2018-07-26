<?php declare(strict_types=1);

namespace AppBundle\Controller\Rest;

use ATS\CoreBundle\Controller\Rest\BaseRestController;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;

class UserController extends BaseRestController
{

    /**
     * @Route("/me", methods="GET")
     *
     * @return JsonResponse
     */
    public function getMeAction()
    {
        //d(['foo' => 'bar']);
        $session = new Session();
        $session->start();
        $data = $session->get('userData');
        if ($data['timeout'] >= time()) {
            return new JsonResponse($data);
        } else {
            return new JsonResponse(['result' => "Not authenticated"], 401);
        }
    }

    public function isNotPublic()
    {
        return true;
    }
}
