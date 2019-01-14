<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Document\Invitation;
use AppBundle\Service\InvitationService;
use ATS\CoreBundle\Controller\Rest\BaseRestController;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InvitationController extends BaseRestController
{
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

        return $this->prepareJsonResponse($data);
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

        return $this->prepareJsonResponse($data);
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

        return $this->prepareJsonResponse($successful);
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

        return $this->prepareJsonResponse($successful);
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

        return $this->prepareJsonResponse(null);
    }
}
