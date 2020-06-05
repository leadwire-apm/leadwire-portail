<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Document\Invitation;
use AppBundle\Service\InvitationService;
use ATS\CoreBundle\Controller\Rest\RestControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Service\ApplicationService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use AppBundle\Document\AccessLevel;

class InvitationController extends Controller
{
    use RestControllerTrait;

    /**
     * @Route("/{id}/get", methods="GET")
     *
     * @param Request $request
     * @param InvitationService $invitationService
     * @param string  $id
     *
     * @return Response
     */
    public function getInvitationAction(Request $request, InvitationService $invitationService, $id)
    {
        $data = $invitationService->getInvitation($id);

        return $this->renderResponse($data);
    }

    /**
     * @Route("/list", methods="GET")
     *
     * @param Request $request
     * @param InvitationService $invitationService
     *
     * @return Response
     */
    public function listInvitationsAction(Request $request, InvitationService $invitationService)
    {
        $data = $invitationService->listInvitations();

        return $this->renderResponse($data);
    }

    /**
     * @Route("/{envId}/new", methods="POST")
     *
     * @param Request $request
     * @param InvitationService $invitationService
     * @param ApplicationService $applicationService
     * @param string $envId
     * @return Response
     */
    public function newInvitationAction(
        Request $request, 
        InvitationService $invitationService,
        ApplicationService $applicationService,
        $envId)
    {
        $user = $this->getUser();
        $data = $request->getContent();
        $app = json_decode($data, true);

        if($applicationService->userHasPermission(
            $app["application"]["id"], 
            $user, 
            $envId, 
            array(AccessLevel::ADMIN,AccessLevel::EDITOR))){
            $successful = $invitationService->newInvitation($data, $this->getUser());
            return $this->renderResponse($successful);
        } else {
            return $this->exception(['message' => "You dont have rights permissions"], 400);
        }
    }
    /**
     * @Route("/{id}/update", methods="PUT")
     *
     * @param Request $request
     * @param InvitationService $invitationService
     *
     * @return Response
     */
    public function updateInvitationAction(Request $request, InvitationService $invitationService)
    {
        $data = $request->getContent();
        $successful = $invitationService->updateInvitation($data);

        return $this->renderResponse($successful);
    }

    /**
     * @Route("/{id}/delete", methods="POST")
     *
     * @param Request $request
     * @param InvitationService $invitationService
     * @param ApplicationService $applicationService
     * @param string $id
     *
     * @return Response
     */
    public function deleteInvitationAction(
        Request $request, 
        InvitationService $invitationService,
        ApplicationService $applicationService,
        $id)
    {
        $data = $request->getContent();
        $_data = \json_decode($data, true);
        $user = $this->getUser();
        if($applicationService->userHasPermission(
            $_data["appId"], 
            $user, 
            $_data["envId"],
            array(AccessLevel::ADMIN,AccessLevel::EDITOR))){
                $invitationService->deleteInvitation($id);
                return $this->renderResponse(null);
        } else {
            return $this->exception(['message' => "You dont have rights permissions"], 400);
        }

    }

    /**
     * @Route("/{id}/accept", methods="POST")
     *
     * @param Request $request
     * @param InvitationService $invitationService
     * @param string $id
     *
     * @return Response
     */
    public function acceptInvitationAction(
        Request $request,
        InvitationService $invitationService,
        $id
    ) {
        $user = json_decode($request->getContent());
        $invitationService->acceptInvitation($id, $user->userId);
        return $this->renderResponse(null);
    }
}
