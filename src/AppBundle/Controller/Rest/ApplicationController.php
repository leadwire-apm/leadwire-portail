<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Service\ApplicationService;
use AppBundle\Service\AuthService;
use AppBundle\Service\ElasticSearchService;
use AppBundle\Service\StatService;
use ATS\CoreBundle\Controller\Rest\BaseRestController;
use FOS\RestBundle\Controller\Annotations\Route;
use MongoDuplicateKeyException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApplicationController extends BaseRestController
{

    /**
     * @Route("/{id}/get", methods="GET")
     *
     * @param Request $request
     * @param ApplicationService $applicationService
     * @param string  $id
     *
     * @return Response
     */
    public function getAppAction(Request $request, ApplicationService $applicationService, $id)
    {
        $data = $applicationService->getApplication($id);

        return $this->prepareJsonResponse($data, 200, "Default");
    }

    /**
     * @Route("/{id}/dashboards", methods="GET")
     *
     * @param Request $request
     * @param ApplicationService $applicationService
     * @param string  $id
     *
     * @return Response
     */
    public function getDashboardsAction(Request $request, ApplicationService $applicationService, ElasticSearchService $elastic, $id)
    {
        $app = $applicationService->getApplication($id);
        if ($app === null) {
            throw new HttpException(404, "App not Found");
        } else {
            return $this->json($elastic->getDashboads($app));
        }
    }

    /**
     * @Route("/{id}/stats", methods="GET")
     *
     * @param StatService $statService
     * @param ApplicationService $applicationService
     * @param string  $id
     *
     * @return Response
     */
    public function getStatsAction(StatService $statService, ApplicationService $applicationService, $id)
    {
        $app = $applicationService->getApplication($id);

        if ($app === null) {
            throw new HttpException(404);
        }

        return $this->prepareJsonResponse(
            $statService->getStats(['app' => $app]),
            200,
            "Default"
        );
    }

    /**
     * @Route("/{id}/activate", methods="POST")
     *
     * @param Request $request
     * @param ApplicationService $applicationService
     * @param string  $id
     *
     * @return Response
     */
    public function activationAppAction(Request $request, ApplicationService $applicationService, $id)
    {
        $app = $applicationService->activateApplication($id, json_decode($request->getContent()));

        if ($app !== null) {
            return $this->prepareJsonResponse($app, 200, "Default");
        } else {
            return $this->prepareJsonResponse($app, 400, "Default");
        }
    }

    /**
     * @Route("/list", methods="GET")
     *
     * @param Request $request
     * @param ApplicationService $applicationService
     *
     * @return Response
     */
    public function listAppsAction(Request $request, ApplicationService $applicationService)
    {
        $user = $this->getUser();
        $data = array_merge($applicationService->invitedListApps($user), $applicationService->listApps($user));
        return $this->prepareJsonResponse($data, 200, "Default");
    }

    /**
     * @Route("/invited/list", methods="GET")
     *
     * @param Request $request
     * @param ApplicationService $applicationService
     *
     * @return Response
     */
    public function invitedListAppsAction(Request $request, ApplicationService $applicationService)
    {
        $data = $applicationService->invitedListApps($this->getUser());

        return $this->prepareJsonResponse($data);
    }

    /**
     * @Route("/new", methods="POST")
     *
     * @param Request $request
     * @param ApplicationService $applicationService
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function newAppAction(Request $request, ApplicationService $applicationService)
    {
        try {
            $data = $request->getContent();
            $application = $applicationService->newApp($data, $this->getUser());

            if ($application !== null) {
                return new JsonResponse($application);
            } else {
                return new JsonResponse(false);
            }
        } catch (MongoDuplicateKeyException $e) {
            return $this->exception("App Name is not Unique");
        }
    }

    /**
     * @Route("/{id}/update", methods="PUT")
     *
     * @param Request $request
     * @param ApplicationService $applicationService
     *
     * @param string $id
     * @return Response
     */
    public function updateAppAction(Request $request, ApplicationService $applicationService, string $id)
    {
        try {
            $data = $request->getContent();
            $successful = $applicationService->updateApp($data, $id);

            return new JsonResponse($successful);
        } catch (MongoDuplicateKeyException $e) {
            return $this->exception("App Name is not Unique");
        }
    }

    /**
     * @Route("/{id}/delete", methods="DELETE")
     *
     * @param Request $request
     * @param ApplicationService $applicationService
     * @param string $id
     *
     * @return Response
     */
    public function deleteAppAction(Request $request, ApplicationService $applicationService, $id)
    {
        $applicationService->deleteApp($id);

        return new JsonResponse(null, 200);
    }

    private function exception($message, $status = 400)
    {
        return new JsonResponse(array('message' => $message), $status);
    }
}
