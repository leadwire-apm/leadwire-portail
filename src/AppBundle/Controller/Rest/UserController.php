<?php declare(strict_types=1);

namespace AppBundle\Controller\Rest;

use AppBundle\Service\AuthService;
use AppBundle\Service\UserService;
use ATS\CoreBundle\Controller\Rest\BaseRestController;
use ATS\PaymentBundle\Exception\OmnipayException;
use FOS\RestBundle\Controller\Annotations\Route;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
        try {
            $data = $request->getContent();
            $successful = $userService->subscribe($data, $this->getUser());

            return $this->prepareJsonResponse($successful);
        } catch (\Exception $e) {
            throw new HttpException(400, $e->getMessage());
        }
    }

    /**
     * @Route("/{id}/invoices", methods="GET")
     *
     * @param UserService $userService
     * @return Response
     */
    public function getInvoicesAction(UserService $userService)
    {
        try {
            $data = $userService->getInvoices($this->getUser());

            return $this->prepareJsonResponse($data);
        } catch (\Exception $e) {
            throw new HttpException(400, $e->getMessage());
        }
    }

    /**
     * @Route("/{id}/subscription", methods="GET")
     *
     * @param UserService $userService
     * @return Response
     */
    public function getSubscriptionAction(UserService $userService)
    {
        try {
            $data = $userService->getSubscription($this->getUser());
            return new JsonResponse($data, 200);
        } catch (\Exception $e) {
            throw new HttpException(400, $e->getMessage());
        }
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
            $data = $userService->updateSubscription(
                $this->getUser(),
                json_decode($request->getContent(), true)
            );
            return $this->json($data);
        } catch (\Exception $e) {
            throw new HttpException(400, $e->getMessage());
        }
    }

    /**
     * @Route("/{id}/creditCard", methods="PUT")
     *
     * @param Request $request
     * @param UserService $userService
     * @return Response
     * @throws HttpException
     */
    public function updateCreditCardAction(Request $request, UserService $userService)
    {
        try {
            $data = $userService->updateCreditCard(
                $this->getUser(),
                json_decode($request->getContent(), true)
            );
            return $this->json($data);
        } catch (\Exception $e) {
            throw new HttpException(400, $e->getMessage());
        }
    }
}
