<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Document\User;
use AppBundle\Service\UserService;
use ATS\CoreBundle\Controller\Rest\RestControllerTrait;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserController extends Controller
{
    use RestControllerTrait;

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

        return $this->renderResponse($user, 200, []);
    }

    /**
     * @Route("/list/{role}", methods="GET", defaults={"role"="all"}, requirements={"role"="all|admin"})
     *
     * @return Response
     */
    public function listUsersAction(Request $request, UserService $userService, $role)
    {
        $users = $userService->listUsersByRole($role);

        return $this->renderResponse($users, Response::HTTP_OK, []);
    }

    /**
     * @Route("/{id}/get", methods="GET")
     *
     * @param Request $request
     * @param UserService $userService
     * @param string $id
     *
     * @return Response
     */
    public function getUserAction(Request $request, UserService $userService, $id)
    {
        $user = $userService->getUser($id);

        return $this->renderResponse($user);
    }

    /**
     * @Route("/{id}/update", methods="PUT")
     *
     * @param Request $request
     * @param UserService $userService
     * @param string $id
     * @return Response
     */
    public function updateUserAction(Request $request, UserService $userService, $id)
    {
        $data = $request->getContent();
        $successful = $userService->updateUser($data, $id);

        return $this->renderResponse($successful);
    }

    /**
     * @Route("/{id}/subscribe", methods="POST")
     *
     * @param Request $request
     * @param UserService $userService
     * @param string $id
     *
     * @return Response
     */
    public function subscribeAction(Request $request, UserService $userService, $id)
    {
        try {
            $data = $request->getContent();
            $successful = $userService->subscribe($data, $this->getUser());

            return $this->renderResponse($successful);
        } catch (\Exception $e) {
            return $this->exception($e->getMessage(), 400);
        }
    }

    /**
     * @Route("/{id}/invoices", methods="GET")
     *
     * @param UserService $userService
     *
     * @return Response
     */
    public function getInvoicesAction(UserService $userService)
    {
        try {
            $data = $userService->getInvoices($this->getUser());

            return $this->renderResponse($data);
        } catch (\Exception $e) {
            return $this->exception($e->getMessage(), 400);
        }
    }

    /**
     * @Route("/{id}/subscription", methods="GET")
     *
     * @param UserService $userService
     *
     * @return Response
     */
    public function getSubscriptionAction(UserService $userService)
    {
        try {
            $data = $userService->getSubscription($this->getUser());

            return $this->renderResponse($data);
        } catch (\Exception $e) {
            return $this->exception($e->getMessage(), 400);
        }
    }

    /**
     * @Route("/{id}/subscribe", methods="PUT")
     *
     * @param Request $request
     * @param UserService $userService
     *
     * @return Response
     *
     * @throws BadRequestHttpException
     */
    public function updateSubscriptionAction(Request $request, UserService $userService)
    {
        try {
            $data = $userService->updateSubscription(
                $this->getUser(),
                json_decode($request->getContent(), true)
            );

            return $this->renderResponse($data);
        } catch (\Exception $e) {
            return $this->exception($e->getMessage(), 400);
        }
    }

    /**
     * @Route("/{id}/creditCard", methods="PUT")
     *
     * @param Request $request
     * @param UserService $userService
     *
     * @return Response
     *
     * @throws BadRequestHttpException
     */
    public function updateCreditCardAction(Request $request, UserService $userService)
    {
        try {
            $data = $userService->updateCreditCard(
                $this->getUser(),
                json_decode($request->getContent(), true)
            );

            return $this->renderResponse($data);
        } catch (\Exception $e) {
            return $this->exception($e->getMessage(), 400);
        }
    }

    private function exception($message, $status = 400)
    {
        return $this->renderResponse(array('message' => $message), $status);
    }

    /**
     * @Route("/{id}/delete", methods="DELETE")
     *
     * @param Request $request
     * @param UserService $userService
     * @param string $id
     *
     * @return Response
     */
    public function deleteUserAction(Request $request, UserService $userService, $id)
    {
        $this->denyAccessUnlessGranted([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
        $successful = $userService->softDeleteUser($id);

        return $this->renderResponse($successful);
    }

    /**
     * @Route("/{id}/lock-toggle", methods="PUT")
     *
     * @param Request $request
     * @param UserService $userService
     * @param string $id
     *
     * @return Response
     */
    public function lockToggleUserAction(Request $request, UserService $userService, $id)
    {
        $this->denyAccessUnlessGranted([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);

        $lockMessage = $request->get("message");

        if ($lockMessage === null) {
            $lockMessage = $this->getParameter('default_lock_message');
        }

        $successful = $userService->lockToggle($id, $lockMessage);

        return $this->renderResponse($successful);
    }
}
