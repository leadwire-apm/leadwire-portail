<?php declare (strict_types = 1);

namespace AppBundle\Controller\Rest;

use AppBundle\Document\Application;
use AppBundle\Document\User;
use AppBundle\Exception\DuplicateApplicationNameException;
use AppBundle\Service\ApplicationService;
use AppBundle\Service\ElasticSearchService;
use AppBundle\Service\KibanaService;
use AppBundle\Service\LdapService;
use AppBundle\Service\SearchGuardService;
use AppBundle\Service\StatService;
use ATS\CoreBundle\Controller\Rest\RestControllerTrait;
use MongoDuplicateKeyException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Manager\ApplicationManager;
use AppBundle\Service\ProcessService;
use AppBundle\Service\CuratorService;

class ApplicationController extends Controller
{

    use RestControllerTrait;

    /**
     * @Route("/{id}/get/{group}", methods="GET", defaults={"group"="Default"})
     *
     * @param Request $request
     * @param ApplicationService $applicationService
     * @param string  $id
     *
     * @return Response
     */
    public function getApplicationAction(Request $request, ApplicationService $applicationService, $id, $group)
    {
        $data = $applicationService->getApplication($id);

        return $this->renderResponse($data, Response::HTTP_OK, [$group]);
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
    public function getApplicationDashboardsAction(
        Request $request,
        ApplicationService $applicationService,
        ElasticSearchService $esService,
        $id
    ) {
        $app = $applicationService->getApplication($id);
        if ($app === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "App not Found");
        } else {
            $dashboards = $esService->getDashboads($app, $this->getUser());
            return $this->renderResponse($dashboards);
        }
    }

    /**
     * @Route("/{id}/reports", methods="GET")
     *
     * @param Request $request
     * @param ApplicationService $applicationService
     * @param string  $id
     *
     * @return Response
     */
    public function getApplicationReportsAction(
        Request $request,
        ApplicationService $applicationService,
        ElasticSearchService $esService,
        $id
    ) {
        $app = $applicationService->getApplication($id);
        if ($app === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "App not Found");
        } else {
            $reports = $esService->getReports($app, $this->getUser());
            return $this->renderResponse($reports);
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
    public function getApplicationStatsAction(StatService $statService, ApplicationService $applicationService, $id)
    {
        $app = $applicationService->getApplication($id);

        if ($app === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND);
        }

        return $this->renderResponse(
            $statService->getStats(['application.id' => $app->getId()]),
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
    public function activateAppAction(Request $request, ApplicationService $applicationService, $id)
    {
        $activationCode = json_decode($request->getContent())->code;
        $app = $applicationService->activateApplication($id, $activationCode);

        if ($app !== null) {
            return $this->renderResponse($app, Response::HTTP_OK);
        } else {
            return $this->renderResponse($app, Response::HTTP_BAD_REQUEST);
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
    public function listUserApplicationsAction(Request $request, ApplicationService $applicationService)
    {
        $applications = $applicationService->listUserAccessibleApplciations($this->getUser());

        return $this->renderResponse($applications, Response::HTTP_OK);
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
        $data = $applicationService->listInvitedToApplications($this->getUser());

        return $this->renderResponse($data);
    }

    /**
     * @Route("/new", methods="POST")
     *
     * @param Request $request
     * @param ApplicationService $applicationService
     * @param LdapService $ldapService
     * @param ElasticSearchService $esService
     * @param KibanaService $kibanaService
     * @param SearchGuardService $sgService
     * @param CuratorService $curatorService
     *
     * @return JsonResponse
     */
    public function newApplicationAction(
        Request $request,
        ApplicationService $applicationService,
        LdapService $ldapService,
        ElasticSearchService $esService,
        KibanaService $kibanaService,
        SearchGuardService $sgService,
        CuratorService $curatorService,
        ProcessService $processService
    ) {
        $status = false;
        $application = null;
        $processService->emit("heavy-operations-in-progress", "Creating application settings");
        try {
            $data = $request->getContent();
            $application = $applicationService->newApplication($data, $this->getUser());

            if ($application !== null) {
                // Application created in MongoDB. proceed with LDAP & ES entries
                $processService->emit("heavy-operations-in-progress", "Creating LDAP Entries");
                $ldapService->createApplicationEntry($application);
                $ldapService->registerApplication($this->getUser(), $application);

                $processService->emit("heavy-operations-in-progress", "Creating Index-patterns");
                $esService->deleteIndex($application->getApplicationIndex());
                $esService->createIndexTemplate($application, $applicationService->getActiveApplicationsNames());

                //$esService->createAlias($application);

                $processService->emit("heavy-operations-in-progress", "Creating Kibana Dashboards");
                $kibanaService->loadIndexPatternForApplication(
                    $application,
                    $application->getApplicationIndex()
                );

                $kibanaService->loadDefaultIndex($application->getApplicationIndex(), 'default');
                $kibanaService->makeDefaultIndex($application->getApplicationIndex(), 'default');

                $kibanaService->createApplicationDashboards($application);

                $esService->deleteIndex($application->getSharedIndex());

                $kibanaService->loadIndexPatternForApplication(
                    $application,
                    $application->getSharedIndex()
                );

                $kibanaService->loadDefaultIndex($application->getSharedIndex(), 'default');
                $kibanaService->makeDefaultIndex($application->getSharedIndex(), 'default');

                $processService->emit("heavy-operations-in-progress", "Configuring SearchGuard");
                $sgService->updateSearchGuardConfig();

                $processService->emit("heavy-operations-in-progress", "Updating Curator Configurations");
                $curatorService->updateCuratorConfig();

                $status = true;
            }
            $processService->emit("heavy-operations-done", "Successeded");
        } catch (DuplicateApplicationNameException $e) {
            $processService->emit("heavy-operations-done", "Failed");
            return $this->renderResponse(['message' => $e->getMessage()], Response::HTTP_NOT_ACCEPTABLE);
        } catch (\Exception $e) {
            $processService->emit("heavy-operations-done", "Failed");
            if ($application instanceof Application) {
                $applicationService->obliterateApplication($application);

            }
            return $this->renderResponse(['message' => $e->getMessage()], Response::HTTP_NOT_ACCEPTABLE);
        }

        if ($status === true) {
            return $this->renderResponse($application);
        } else {
            return $this->renderResponse(false);
        }
    }

    /**
     * @Route("/{id}/update", methods="PUT")
     *
     * @param Request $request
     * @param ApplicationService $applicationService
     * @param ElasticSearchService $esService
     * @param KibanaService $kibanaService
     * @param CuratorService $curatorService
     * @param string $id
     *
     * @return Response
     */
    public function updateApplicationAction(
        Request $request,
        ApplicationService $applicationService,
        ElasticSearchService $esService,
        KibanaService $kibanaService,
        CuratorService $curatorService,
        ProcessService $processService,
        string $id
    ) {
        $processService->emit("heavy-operations-in-progress", "Updating application settings");
        try {
            $data = $request->getContent();
            $state = $applicationService->updateApplication($data, $id);
            $esService->getAlias($application);
            if ($state['esUpdateRequired'] === true) {
                $application = $state['application'];
                $processService->emit("heavy-operations-in-progress", "Updating Index-patterns");
                $esService->deleteIndex($application->getApplicationIndex());
                $esService->createIndexTemplate($application, $applicationService->getActiveApplicationsNames());

                //$aliases = $esService->createAlias($application);

                $kibanaService->loadIndexPatternForApplication(
                    $application,
                    $application->getApplicationIndex()
                );

                $processService->emit("heavy-operations-in-progress", "Updating Kibana Dashboards");
                $kibanaService->loadDefaultIndex($application->getApplicationIndex(), 'default');
                $kibanaService->makeDefaultIndex($application->getApplicationIndex(), 'default');

                $kibanaService->createApplicationDashboards($application);

                $esService->deleteIndex($application->getSharedIndex());

                $kibanaService->loadIndexPatternForApplication(
                    $application,
                    $application->getSharedIndex()
                );

                $kibanaService->loadDefaultIndex($application->getSharedIndex(), 'default');
                $kibanaService->makeDefaultIndex($application->getSharedIndex(), 'default');

                $processService->emit("heavy-operations-in-progress", "Updating Curator Configurations");
                $curatorService->updateCuratorConfig();
            }
            $processService->emit("heavy-operations-done", "Succeeded");

            return $this->renderResponse($state['successful']);
        } catch (MongoDuplicateKeyException $e) {
            $processService->emit("heavy-operations-done", "Failed");
            return $this->renderResponse(['message' => "Application's name must be unique"], Response::HTTP_UNAUTHORIZED);
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
    public function deleteApplicationAction(Request $request, ApplicationService $applicationService, $id)
    {
        $application = $applicationService->getApplication($id);

        if ($application !== null) {
            $accessGrantedByRole = $this->getUser()->hasRole(User::ROLE_ADMIN) || $this->getUser()->hasRole(User::ROLE_SUPER_ADMIN);
            $accessGrantedByOwnership = $application->getOwner()->getId() === $this->getUser()->getId();

            if ($accessGrantedByOwnership === true || $accessGrantedByRole === true) {
                $applicationService->deleteApplication($id);

                return $this->renderResponse(null, Response::HTTP_OK);
            } else {
                throw new UnauthorizedHttpException('', "Unauthorized action");
            }
        } else {
            throw new NotFoundHttpException("Application [$id] not found");
        }
    }

    /**
     * @Route("/{id}/remove", methods="DELETE")
     *
     * @param Request $request
     * @param ApplicationService $applicationService
     * @param string $id
     *
     * @return Response
     */
    public function removeApplicationAction(Request $request, ApplicationService $applicationService, $id)
    {
        $applicationService->removeUserApplication($id, $this->getUser());

        return new JsonResponse(null, Response::HTTP_ACCEPTED);
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

        $applications = $applicationService->getApplications();

        return $this->renderResponse($applications, Response::HTTP_OK);
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

    /**
     * @Route("/{id}/apply-change", methods="PUT")
     *
     * @param Request $request
     * @param ApplicationService $applicationService
     * @param ApplicationManager $applicationManager
     * @param ElasticSearchService $esService
     * @param KibanaService $kibanaService
     * @param string $id
     *
     * @return Response
     */
    public function applyTypeChangeAction(
        Request $request,
        ApplicationService $applicationService,
        ApplicationManager $applicationManager,
        ElasticSearchService $esService,
        KibanaService $kibanaService,
        ProcessService $processService,
        string $id
    ) {
        $application = $applicationService->getApplication($id);
        $processService->emit("heavy-operations-in-progress", "Updating Application Type");
        if ($application instanceof Application) {
            $processService->emit("heavy-operations-in-progress", "Updating Index-patterns");
            $esService->deleteIndex($application->getApplicationIndex());
            $esService->createIndexTemplate($application, $applicationService->getActiveApplicationsNames());
           // $aliases = $esService->createAlias($application);
            $processService->emit("heavy-operations-in-progress", "Updating Kibana Dashboards");
            $kibanaService->loadIndexPatternForApplication(
                $application,
                $application->getApplicationIndex()
            );

            $kibanaService->loadDefaultIndex($application->getApplicationIndex(), 'default');
            $kibanaService->makeDefaultIndex($application->getApplicationIndex(), 'default');

            $kibanaService->createApplicationDashboards($application);

            $esService->deleteIndex($application->getSharedIndex());

            $kibanaService->loadIndexPatternForApplication(
                $application,
                $application->getSharedIndex()
            );

            $kibanaService->loadDefaultIndex($application->getSharedIndex(), 'default');
            $kibanaService->makeDefaultIndex($application->getSharedIndex(), 'default');

            $application->setDeployedTypeVersion($application->getType()->getVersion());
            $applicationManager->update($application);
            $processService->emit("heavy-operations-done", "Succeeded");
        } else {
            $processService->emit("heavy-operations-done", "Failed");
            throw new NotFoundHttpException("Application with ID {$id} not found.");
        }

        return $this->renderResponse(true);
    }

    /**
     * @Route("/{id}/update-dashboards", methods="PUT")
     *
     * @param Request $request
     * @param ApplicationService $applicationService
     * @param string $id
     */
    public function updateApplicationActionDashboards( Request $request, ApplicationService $applicationService, string $id){
        try{
            $data = $request->getContent();
            $state = $applicationService->updateApplicationDashboards($data, $id, $this->getUser()->getId());
            return $this->renderResponse($state['successful']);
        } catch (MongoDuplicateKeyException $e) {
            return $this->renderResponse(['message' => $e->getMessage()], Response::HTTP_NOT_ACCEPTABLE);
        }
    }

    /**
     * @Route("/{app}/{env}/documents", methods="GET")
     *
     * @param Request $request
     * @param ElasticSearchService $esService
     * @param string $app
     * @param string $env
     */
    public function getApplicationDocumentsCount( Request $request, ElasticSearchService $esService, string $app, string $env){
        try{
            $data = $esService->getApplicationTransactions($app, $env);
            return $this->renderResponse($data);
        } catch (MongoDuplicateKeyException $e) {
            return $this->renderResponse(['message' => $e->getMessage()], Response::HTTP_NOT_ACCEPTABLE);
        }
    }
}
