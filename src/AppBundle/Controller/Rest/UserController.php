<?php declare(strict_types=1);

namespace AppBundle\Controller\Rest;

use AppBundle\Service\AuthService;
use AppBundle\Service\UserService;
use ATS\CoreBundle\Controller\Rest\BaseRestController;
use ATS\PaymentBundle\Exception\OmnipayException;
use FOS\RestBundle\Controller\Annotations\Route;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use SensioLabs\Security\Exception\HttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function getMeAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user) {
            throw new HttpException("Non Authorized", 401);
        }

        return $this->prepareJsonResponse($user, 200, "Default");
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
     * @Route("/{id}/subscribe", methods="POST")
     *
     * @param Request $request
     * @param UserService $userService
     *
     * @param $id
     * @return Response
     */
    public function subscribeAction(Request $request, UserService $userService, $id)
    {
        $data = $request->getContent();
        $successful = $userService->subscribe($data, $this->getUser());

        return $this->prepareJsonResponse($successful);
    }

    /**
     * @Route("/{id}/invoices", methods="GET")
     *
     * @param UserService $userService
     * @return Response
     */
    public function getInvoicesAction(UserService $userService)
    {
        $data = $userService->getInvoices($this->getUser());

        return $this->prepareJsonResponse($data);
    }

    /**
     * @Route("/{id}/subscription", methods="GET")
     *
     * @param UserService $userService
     * @return Response
     */
    public function getSubscriptionAction(UserService $userService)
    {
        $data = $userService->getSubscription($this->getUser());
        return new JsonResponse($data, 200);
    }

    /**
     * @Route("/{id}/subscribe", methods="PUT")
     *
     * @param Request $request
     * @param UserService $userService
     * @return Response
     * @throws HttpException
     */
    public function updateSubscriptionAction(Request $request, UserService $userService)
    {
        try {
            $data = $userService->updateSubscription($this->getUser(), json_decode($request->getContent(), true));
            return $this->json($data);
        } catch (\Exception $e) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(400, $e->getMessage());
        }
    }
}
