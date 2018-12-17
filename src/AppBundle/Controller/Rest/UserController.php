<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Service\UserService;
use ATS\CoreBundle\Controller\Rest\BaseRestController;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserController extends BaseRestController
{

    /**
     * @Route("/me", methods="GET")
     *
     * @return Response
     */
    public function getMeAction()
    {
        $user = $this->getUser();
        if ($user === null) {
            return $this->exception("Non Authorized", 401);
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
            return $this->exception($e->getMessage(), 400);
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
            return $this->exception($e->getMessage(), 400);
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
            return $this->json($data, 200);
        } catch (\Exception $e) {
            return $this->exception($e->getMessage(), 400);
        }
    }

    /**
     * @Route("/{id}/subscribe", methods="PUT")
     *
     * @param Request $request
     * @param UserService $userService
     * @return Response
     * @throws BadRequestHttpException
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
            return $this->exception($e->getMessage(), 400);
        }
    }

    /**
     * @Route("/{id}/creditCard", methods="PUT")
     *
     * @param Request $request
     * @param UserService $userService
     * @return Response
     * @throws BadRequestHttpException
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
            return $this->exception($e->getMessage(), 400);
        }
    }

    private function exception($message, $status = 400)
    {
        return new JsonResponse(array('message' => $message), $status);
    }
}
