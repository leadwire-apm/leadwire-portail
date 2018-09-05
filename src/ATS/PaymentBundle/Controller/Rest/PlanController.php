<?php declare(strict_types=1);

namespace ATS\PaymentBundle\Controller\Rest;

use ATS\CoreBundle\Controller\Rest\BaseRestController;
use ATS\PaymentBundle\Document\Plan;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ATS\CoreBundle\Service\Voter\AclVoter;
use ATS\PaymentBundle\Service\PlanService;

class PlanController extends BaseRestController
{
    /**
     * @Route("/{id}/get", methods="GET")
     *
     * @param Request $request
     * @param PlanService $planService
     * @param string  $id
     *
     * @return Response
     */
    public function getPlanAction(Request $request, PlanService $planService, $id)
    {
        $data = $planService->getPlan($id);
        $this->denyAccessUnlessGranted(AclVoter::VIEW, $data);

        return $this->prepareJsonResponse($data);
    }

    /**
     * @Route("/list", methods="GET")
     *
     * @param Request $request
     * @param PlanService $planService
     *
     * @return Response
     */
    public function listPlansAction(Request $request, PlanService $planService)
    {
        $this->denyAccessUnlessGranted(AclVoter::VIEW_ALL, Plan::class);
        $data = $planService->listPlans();

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
     * @param PlanService $planService
     * @param int $pageNumber
     * @param int $itemsPerPage
     *
     * @return Response
     */
    public function paginatePlansAction(
        Request $request,
        PlanService $planService,
        $pageNumber,
        $itemsPerPage
    ) {
        $this->denyAccessUnlessGranted(AclVoter::VIEW_ALL, Plan::class);
        $pageResult = $planService->paginate($pageNumber, $itemsPerPage);

        return $this->prepareJsonResponse($pageResult);
    }
}
