<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Service\ElasticSearchService;
use ATS\CoreBundle\Controller\Rest\RestControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OverviewController extends Controller
{

    use RestControllerTrait;

    /**
     * @Route("/getClusterInformations", methods="GET")
     *
     * @param Request $request
     * 
     * @return Response
     */
    public function getClusterInformations(Request $request, ElasticSearchService $esService) {
        $data = $esService->getClusterInformations();
        return $this->renderResponse($data);
    }

    public function getRcaOverview(Request $request, ElasticSearchService $esService) {
        $data = $esService->getRcaOverview();
        return $this->renderResponse($data);
    }
}
