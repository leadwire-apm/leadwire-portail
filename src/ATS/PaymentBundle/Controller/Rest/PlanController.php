<?php declare (strict_types = 1);

namespace ATS\PaymentBundle\Controller\Rest;

use ATS\CoreBundle\Controller\Rest\RestControllerTrait;
use ATS\PaymentBundle\Document\Plan;
use ATS\PaymentBundle\Service\PlanService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PlanController extends Controller
{
    use RestControllerTrait;
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

        return $this->renderResponse($data);
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
        $data = $planService->listPlans();

        return $this->renderResponse($data);
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
        $pageResult = $planService->paginate($pageNumber, $itemsPerPage);

        return $this->renderResponse($pageResult);
    }
}
