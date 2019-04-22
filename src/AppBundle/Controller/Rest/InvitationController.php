<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Document\Invitation;
use AppBundle\Service\InvitationService;
use AppBundle\Service\SearchGuardService;
use ATS\CoreBundle\Controller\Rest\RestControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route("/new", methods="POST")
     *
     * @param Request $request
     * @param InvitationService $invitationService
     *
     * @return Response
     */
    public function newInvitationAction(Request $request, InvitationService $invitationService)
    {
        $data = $request->getContent();
        $successful = $invitationService->newInvitation($data, $this->getUser());

        return $this->renderResponse($successful);
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
     * @Route("/{id}/delete", methods="DELETE")
     *
     * @param Request $request
     * @param InvitationService $invitationService
     * @param string $id
     *
     * @return Response
     */
    public function deleteInvitationAction(Request $request, InvitationService $invitationService, $id)
    {
        $invitationService->deleteInvitation($id);

        return $this->renderResponse(null);
    }

    /**
     * @Route("/{id}/accept", methods="POST")
     *
     * @param Request $request
     * @param InvitationService $invitationService
     * @param SearchGuardService $sgService
     * @param string $id
     *
     * @return Response
     */
    public function acceptInvitationAction(
        Request $request,
        InvitationService $invitationService,
        SearchGuardService $sgService,
        $id
    ) {
        $user = json_decode($request->getContent());
        $invitationService->acceptInvitation($id, $user->userId);
        $sgService->updateSearchGuardConfig();

        return $this->renderResponse(null);
    }
}
