<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Service\MonitoringSetService;
use ATS\CoreBundle\Controller\Rest\RestControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MonitoringSetController extends Controller
{
    use RestControllerTrait;
    /**
     * @Route("/{id}/get", methods="GET")
     *
     * @param Request $request
     * @param MonitoringSetService $monitoringSetService
     * @param string  $id
     *
     * @return Response
     */
    public function getMonitoringSetAction(Request $request, MonitoringSetService $monitoringSetService, $id)
    {
        $data = $monitoringSetService->getMonitoringSet($id);

        return $this->renderResponse($data);
    }

    /**
     * @Route("/list", methods="GET")
     *
     * @param Request $request
     * @param MonitoringSetService $monitoringSetService
     *
     * @return Response
     */
    public function listMonitoringSetsAction(Request $request, MonitoringSetService $monitoringSetService)
    {
        $data = $monitoringSetService->listMonitoringSets();

        return $this->renderResponse($data);
    }

    /**
     * @Route("/new", methods="POST")
     *
     * @param Request $request
     * @param MonitoringSetService $monitoringSetService
     *
     * @return Response
     */
    public function newMonitoringSetAction(Request $request, MonitoringSetService $monitoringSetService)
    {
        try {
            $data = $request->getContent();
            $successful = $monitoringSetService->newMonitoringSet($data);

            return $this->renderResponse($successful);
        } catch (\Exception $e) {
            return $this->renderResponse($e->getMessage());
        }
    }

    /**
     * @Route("/{id}/update", methods="PUT")
     *
     * @param Request $request
     * @param MonitoringSetService $monitoringSetService
     *
     * @return Response
     */
    public function updateMonitoringSetAction(Request $request, MonitoringSetService $monitoringSetService)
    {
        $data = $request->getContent();
        $successful = $monitoringSetService->updateMonitoringSet($data);

        return $this->renderResponse($successful);
    }

    /**
     * @Route("/{id}/delete", methods="DELETE")
     *
     * @param Request $request
     * @param MonitoringSetService $monitoringSetService
     * @param string $id
     *
     * @return Response
     */
    public function deleteMonitoringSetAction(Request $request, MonitoringSetService $monitoringSetService, $id)
    {
        $monitoringSetService->deleteMonitoringSet($id);

        return $this->renderResponse(null);
    }
}
