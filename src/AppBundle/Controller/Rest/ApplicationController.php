<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Document\User;
use AppBundle\Service\ApplicationService;
use AppBundle\Service\ElasticSearchService;
use AppBundle\Service\StatService;
use ATS\CoreBundle\Controller\Rest\RestControllerTrait;
use MongoDuplicateKeyException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

class ApplicationController extends Controller
{

    use RestControllerTrait;

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

        return $this->renderResponse($data, Response::HTTP_OK, ["Default"]);
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

        return $this->renderResponse(
            $statService->getStats(['app.$id' => $app->getId()]),
            Response::HTTP_OK,
            ["Default"]
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
            return $this->renderResponse($app, Response::HTTP_OK, ["Default"]);
        } else {
            return $this->renderResponse($app, 400, ["Default"]);
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

        return $this->renderResponse($data, Response::HTTP_OK, ["Default"]);
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

        return $this->renderResponse($data);
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
                return $this->renderResponse($application);
            } else {
                return $this->renderResponse(false);
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

            return $this->renderResponse($successful);
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

        return $this->renderResponse(null, Response::HTTP_OK);
    }

    private function exception($message, $status = 400)
    {
        return $this->renderResponse(array('message' => $message), $status);
    }

    /**
     * @Route("/all", methods="GET")
     *
     * @param Request $request
     * @param ApplicationService $applicationService
     *
     * @return Response
     */
    public function getAllApplicationsAction(Request $request, ApplicationService $applicationService)
    {
        $this->denyAccessUnlessGranted([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);

        $applications = $applicationService->getApps();

        return $this->renderResponse($applications, Response::HTTP_OK, ["Default"]);
    }

    /**
     * @Route("/{id}/activate-toggle", methods="PUT")
     *
     * @param Request $request
     * @param ApplicationService $applicationService
     *
     * @return Response
     */
    public function toggleApplicationActivationAction(Request $request, ApplicationService $applicationService, $id)
    {
        $this->denyAccessUnlessGranted([User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);

        $successful = $applicationService->toggleActivation($id);

        return $this->renderResponse($successful, Response::HTTP_OK);
    }
}
