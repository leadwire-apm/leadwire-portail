<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Document\Invitation;
use AppBundle\Service\InvitationService;
use ATS\CoreBundle\Controller\Rest\BaseRestController;
use ATS\CoreBundle\Service\Voter\AclVoter;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
        $this->denyAccessUnlessGranted(AclVoter::VIEW, $data);

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
        $this->denyAccessUnlessGranted(AclVoter::VIEW_ALL, Invitation::class);
        $data = $invitationService->listInvitations();

        return $this->prepareJsonResponse($data);
    }

    /**
     * @Route(
     *    "/paginate/{pageNumber}/{itemsPerPage}",
     *    methods="GET",
     *    defaults={"pageNumber" = 1, "itemsPerPage" = 20}
     * )
     *
     * @param Request $request
     * @param InvitationService $invitationService
     * @param int $pageNumber
     * @param int $itemsPerPage
     *
     * @return Response
     */
    public function paginateInvitationsAction(
        Request $request,
        InvitationService $invitationService,
        $pageNumber,
        $itemsPerPage
    ) {
        $this->denyAccessUnlessGranted(AclVoter::VIEW_ALL, Invitation::class);
        $pageResult = $invitationService->paginate($pageNumber, $itemsPerPage);

        return $this->prepareJsonResponse($pageResult);
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
        $this->denyAccessUnlessGranted(AclVoter::CREATE, Invitation::class);
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
        $this->denyAccessUnlessGranted(AclVoter::DELETE, Invitation::class);
        $invitationService->deleteInvitation($id);

        return $this->prepareJsonResponse([]);
    }

    /**
     * @Route("/{lang}/{term}/search", methods="GET", defaults={"lang" = "en"})
     *
     * @param Request $request
     * @param InvitationService $invitationService
     * @param string $term
     * @param string $lang
     *
     * @return Response
     */
    public function searchInvitationAction(Request $request, InvitationService $invitationService, $term, $lang)
    {
        $this->denyAccessUnlessGranted(AclVoter::SEARCH, Invitation::class);

        try {
            $result = $todoService->textSearch($term, $lang);
        } catch (\MongoException $e) {
            throw new BadRequestHttpException("Entity " . Invitation::class . " is not searchable. ");
        }

        return $this->prepareJsonResponse($invitationService->textSearch($term, $lang));
    }
}
