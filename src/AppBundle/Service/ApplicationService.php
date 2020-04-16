<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\Application;
use AppBundle\Document\ApplicationPermission;
use AppBundle\Document\DeleteTask;
use AppBundle\Document\Task;
use AppBundle\Document\User;
use AppBundle\Document\Dashboard;
use AppBundle\Exception\DuplicateApplicationNameException;
use AppBundle\Manager\ActivationCodeManager;
use AppBundle\Manager\ApplicationManager;
use AppBundle\Manager\ApplicationPermissionManager;
use AppBundle\Manager\DeleteTaskManager;
use AppBundle\Manager\DashboardManager;
use AppBundle\Service\ActivationCodeService;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use AppBundle\Manager\ApplicationTypeManager;
use AppBundle\Service\EnvironmentService;
use AppBundle\Document\AccessLevel;
use AppBundle\Manager\UserManager;
use AppBundle\Manager\AccessLevelManager;

use AppBundle\Service\ProcessService;
use AppBundle\Service\CuratorService;
use AppBundle\Service\ElasticSearchService;
use AppBundle\Service\KibanaService;
use AppBundle\Service\LdapService;
use AppBundle\Service\SearchGuardService;
use AppBundle\Service\StatService;

/**
 * Service class for App entities
 *
 */
class ApplicationService
{
    /**
     * @var ApplicationManager
     */
    private $applicationManager;

    /**
     * @var AccessLevelManager
     */
    private $accessLevelManager;

    /**
     * @var ApplicationTypeManager
     */
    private $applicationTypeManager;

    /**
     * @var ActivationCodeManager
     */
    private $activationCodeManager;

    /**
     * @var ApplicationPermissionManager
     */
    private $apManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ApplicationTypeService
     */
    private $appTypeService;

    /**
     * @var ActivationCodeService
     */
    private $activationCodeService;

    /**
     * @var DeleteTaskManager
     */
    private $taskManager;

    /**
     * @var DashboardManager
     */
    private $dashboardManager;

    /**
     * @var EnvironmentService
     */
    private $environmentService;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var ProcessService
     */
    private $processService;

    /**
     * @var CuratorService
     */
    private $curatorService;

    /**
     * @var ElasticSearchService
     */
    private $elasticSearchService;

    /**
     * @var KibanaService
     */
    private $kibanaService;

    /**
     * @var LdapService
     */
    private $ldapService;

    /**
     * @var SearchGuardService
     */
    private $searchGuardService;

    /**
     * @var StatService
     */
    private $statService;

    /**
     * Constructor
     *
     * @param AccessLevelManager $accessLevelManager
     * @param ApplicationManager $applicationManager
     * @param ApplicationTypeManager $applicationTypeManager
     * @param ActivationCodeManager $activationCodeManager
     * @param DeleteTaskManager $taskManager
     * @param ApplicationPermissionManager $apManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @param ApplicationTypeService $appTypeService
     * @param ActivationCodeService $activationCodeService
     * @param DashboardManager $dashboardManager
     * @param EnvironmentService $environmentService
     * @param UserManager $userManager
     * @param ProcessService $processService
     * @param CuratorService $curatorService
     * @param ElasticSearchService $elasticSearchService
     * @param KibanaService $kibanaService
     * @param LdapService $ldapService
     * @param SearchGuardService $searchGuardService
     * @param StatService $statService
     */
    public function __construct(
        AccessLevelManager $accessLevelManager,
        ApplicationManager $applicationManager,
        ApplicationTypeManager $applicationTypeManager,
        ActivationCodeManager $activationCodeManager,
        DeleteTaskManager $taskManager,
        ApplicationPermissionManager $apManager,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        ApplicationTypeService $appTypeService,
        ActivationCodeService $activationCodeService,
        DashboardManager $dashboardManager,
        EnvironmentService $environmentService,
        UserManager $userManager,
        ProcessService $processService,
        CuratorService $curatorService,
        ElasticSearchService $elasticSearchService,
        KibanaService $kibanaService,
        LdapService $ldapService,
        SearchGuardService $searchGuardService,
        StatService $statService
    ) {
        $this->accessLevelManager = $accessLevelManager;
        $this->applicationManager = $applicationManager;
        $this->applicationTypeManager = $applicationTypeManager;
        $this->activationCodeManager = $activationCodeManager;
        $this->taskManager = $taskManager;
        $this->apManager = $apManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->appTypeService = $appTypeService;
        $this->activationCodeService = $activationCodeService;
        $this->dashboardManager = $dashboardManager;
        $this->environmentService = $environmentService;
        $this->userManager = $userManager;
        $this->processService = $processService;
        $this->curatorService = $curatorService;
        $this->es = $elasticSearchService;
        $this->kibanaService = $kibanaService;
        $this->ldapService = $ldapService;
        $this->sg = $searchGuardService;
        $this->statService = $statService;
    }

    /**
     * List all apps owned by a given user
     *
     * @param User $user
     *
     * @return array
     */
    public function listOwnedApplications(User $user): array
    {
        return $this->applicationManager->getBy(['owner' => $user, 'removed' => false]);
    }

    /**
     * Lists all the applications that the user holds an invitation to
     *
     * @var User $user
     *
     * @return array
     */
    public function listInvitedToApplications(User $user): array
    {
        $applications = [];

        foreach ($user->invitations as $invitation) {
            $application = $invitation->getApplication();
            if ($application->isRemoved() !== false) {
                $applications[] = $application;
            }
        }

        return $applications;
    }

    public function listDemoApplications(): array
    {
        return $this->applicationManager->getBy(['demo' => true]);
    }

    /**
     *
     * @param User $user
     *
     * @return Application[]
     */
    public function listUserAccessibleApplciations(User $user): array
    {
        $accessibleApplications = [];
        $permissions = $this->apManager->getPermissionsForUser($user);

        /** @var ApplicationPermission $permission */
        foreach ($permissions as $permission) {
            if ($permission->getApplication()->isRemoved() === false) {
                $accessibleApplications[] = $permission->getApplication();
            }
        }

        return $accessibleApplications;
    }

    /**
     * Paginates through Apps
     *
     * @codeCoverageIgnore
     * @param int $pageNumber
     * @param int $itemsPerPage
     * @param array $criteria
     *
     * @return array
     */
    public function paginate($pageNumber = 1, $itemsPerPage = 20, array $criteria = [])
    {
        return $this->applicationManager->paginate($criteria, $pageNumber, $itemsPerPage);
    }

    /**
     * Get a specific app
     *
     * @param string $id
     *
     * @return ?Application
     */
    public function getApplication($id): ?Application
    {
        // Very bad programming
        return $this->applicationManager->getOneBy(['_id' => $id, 'removed' => false]);
    }

    /**
     * Activate Disabled App
     *
     * @param string $id
     * @param string $code
     *
     * @return ?Application
     */
    public function activateApplication($id, $code): ?Application
    {
        $result = null;
        $activationCode = $this->activationCodeService->getByCode($code);
        $application = $this->getApplication($id);

        if ($activationCode !== null && $application !== null) {
            $valid = $this->activationCodeService->validateActivationCode($activationCode);

            if ($valid === true) {
                $application->setEnabled(true);
                $activationCode->setApplication($application);
                $activationCode->setUsed(true);
                $this->applicationManager->update($application);
                $this->activationCodeManager->update($activationCode);
                $result = $application;
            }
        }

        return $result;
    }

    /**
     * Get specific apps
     *
     * @codeCoverageIgnore
     * @param array $criteria
     *
     * @return array
     */
    public function getApplications(array $criteria = [])
    {
        return $this->applicationManager->getBy($criteria);
    }

    /**
     * Creates a new app from JSON data
     *
     * @param string $json
     * @param User $user
     *
     * @return ?Application
     *
     * @throws \Exception
     */
    public function newApplication($json, User $user): ?Application
    {
        $context = new DeserializationContext();
        $context->setGroups(['Default']);
        /** @var Application $application */
        $application = $this
            ->serializer
            ->deserialize($json, Application::class, 'json', $context);

        $applicationType = $this->applicationTypeManager->getOneBy(['id' => $application->getType()->getId()]);
        $application->setDeployedTypeVersion($applicationType->getVersion());
        $application->setName(\str_replace(' ', '_', $application->getName())); // Make sure thare are no spaces
        $dbApplication = $this->applicationManager->getOneBy(['name' => $application->getName()]);

        if ($dbApplication !== null) {
            throw new DuplicateApplicationNameException("An application with the same name already exists");
        }

        $uuid1 = Uuid::uuid1();
        $application
            ->setCreatedAt(new \DateTime())
            ->setOwner($user)
            ->setEnabled(false)
            ->setUuid($uuid1->toString())
            ->setRemoved(false);

        foreach ($this->environmentService->getAll() as $environment) {
            $application->addEnvironment($environment);
        }

        /** @var string $applicationTypeId */
        $applicationTypeId = $application->getType()->getId();
        $ap = $this->appTypeService->getApplicationType($applicationTypeId);
        $application->setType($ap);
        $this->applicationManager->update($application);
        
        $applicationPermission = new ApplicationPermission();
        $applicationPermission
            ->setUser($user)
            ->setApplication($application)
            ->setAccess(ApplicationPermission::ACCESS_OWNER)
            ->setModifiedAt(new \DateTime());

        $this->apManager->update($applicationPermission);
        
        foreach ($application->getEnvironments() as $environment) {
            $user->addAccessLevel((new AccessLevel())
                    ->setEnvironment($environment)
                    ->setApplication($application)
                    ->setLevel(AccessLevel::ACCESS)
                    ->setAccess(AccessLevel::EDIT)
                );   
            $user->addAccessLevel((new AccessLevel())
                    ->setEnvironment($environment)
                    ->setApplication($application)
                    ->setLevel(AccessLevel::REPORT)
                    ->setAccess(AccessLevel::EDIT)
                );     
        }
        $this->userManager->update($user);
        $this->createIndexApp($application, $user);
        return $application;
    }

    /**
     * create application index and shared index
     * 
     */
    function createIndexApp(Application $application, User $user){
        if($application !== null){

            foreach ($this->environmentService->getAll() as $environment) {
                $envName = $environment->getName();
                $sharedIndex =  $envName . "-" . $application->getSharedIndex();
                $appIndex =  $envName . "-" . $application->getApplicationIndex();
                $patternIndex = "*-" . $envName . "-" . $application->getName() . "-*";
                $watechrIndex = $envName ."-" . $application->getApplicationWatcherIndex();

                $this->es->createTenant($appIndex);
                $this->es->createIndexTemplate($application, $this->getActiveApplicationsNames(), $envName);
    
    
                $this->kibanaService->loadIndexPatternForApplication(
                    $application,
                    $appIndex,
                    $envName 
                );
    
                $this->kibanaService->loadDefaultIndex($appIndex, 'default');
                $this->kibanaService->makeDefaultIndex($appIndex, 'default');
        
                $this->kibanaService->createApplicationDashboards($application, $envName);
                
                $this->es->createTenant($sharedIndex);
        
                $this->kibanaService->loadIndexPatternForApplication(
                    $application,
                    $sharedIndex,
                    $envName
                );

                $this->es->createTenant($watechrIndex);

                $this->kibanaService->loadDefaultIndex($sharedIndex, 'default');
                $this->kibanaService->makeDefaultIndex($sharedIndex, 'default');

                $this->es->createRole($envName, $application->getName(), array($patternIndex), array($sharedIndex, $appIndex), array("kibana_all_read"), false, false);
                $this->es->createRole($envName, $application->getName(), array($patternIndex), array($sharedIndex, $appIndex), array("kibana_all_write"), true, false);
                $this->es->createRole($envName, $application->getName(), array(), array($watechrIndex), array("kibana_all_write"), true, true);


                $this->es->createRoleMapping($envName, $application->getName(), $user->getName(), false, false);
                $this->es->createRoleMapping($envName, $application->getName(), $user->getName(), true, false); 
                $this->es->createRoleMapping($envName, $application->getName(), $user->getName(), true, true); 
  
            }
        }
    }

    /**
     * Updates a specific app from JSON data
     *
     * @param string $json
     *
     * @return array
     */
    public function updateApplication($json, $id): array
    {
        $state = [
            'successful' => false,
            'esUpdateRequired' => false,
            'application' => null,
        ];

        try {

            $realApp = $this->applicationManager->getOneBy(['id' => $id]);

            if ($realApp === null) {
                throw new \Exception(sprintf("Unknow application %s", $id));
            }

            $context = new DeserializationContext();
            $context->setGroups(['Default']);
            /** @var Application $application */
            $application = $this->serializer->deserialize($json, Application::class, 'json', $context);
            $state['esUpdateRequired'] = $realApp->getType()->getId() !== $application->getType()->getId();
            $newType = $this->appTypeService->getApplicationType((string) $application->getType()->getId());
            $realApp->setDeployedTypeVersion($newType->getVersion());
            $realApp->setType($newType);
            $realApp->setName($application->getName());
            $realApp->setDescription($application->getDescription());

            $this->applicationManager->update($realApp);

            $state['successful'] = true;

            $state['application'] = $realApp;

            $this->updateIndexApp($realApp);

            //$this->curatorService->updateCuratorConfig();


        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $state['successful'] = false;
            $state['esUpdateRequired'] = false;
            $state['application'] = null;
        }

        return $state;
    }

    /**
     * update application index
     */
    function updateIndexApp(Application $application){

        foreach ($application->getEnvironments() as $environment){
           
            $envName = $environment->getName();
            $sharedIndex =  $envName . "-" . $application->getSharedIndex();
            $appIndex =  $envName . "-" . $application->getApplicationIndex();
            
            $this->es->deleteTenant($appIndex);
            $this->es->deleteIndex($appIndex);

            $this->es->createTenant($appIndex);
            $this->es->createIndexTemplate($application, $this->getActiveApplicationsNames(), $envName);
            
            $this->kibanaService->loadIndexPatternForApplication(
                $application,
                $appIndex,
                $envName
            );

            $this->kibanaService->loadDefaultIndex($appIndex, 'default');
            $this->kibanaService->makeDefaultIndex($appIndex, 'default');

            $this->kibanaService->createApplicationDashboards($application, $envName);
            
            $this->kibanaService->loadIndexPatternForApplication(
                $application,
                $sharedIndex,
                $envName
            );

            $this->kibanaService->loadDefaultIndex($sharedIndex, 'default');
            $this->kibanaService->makeDefaultIndex($sharedIndex, 'default');
        }

    }

    /**
     * @param Application $application
     *
     * @return void
     */
    public function obliterateApplication(Application $application)
    {
        $this->apManager->removeApplicationPermissions($application);

        $this->applicationManager->deleteById((string) $application->getId());
    }

    /**
     * Deletes a specific app from JSON data
     *
     * @param string $id
     *
     * @return void
     */
    public function deleteApplication($id)
    {
        $application = $this->applicationManager->getOneBy(['id' => $id]);
        if ($application === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "Application not Found");
        } else {
            $application->setRemoved(true);
            $task = new DeleteTask();
            $task
                ->setApplication($application)
                ->setStatus(Task::STATUS_SCHEDULED);
            $this->taskManager->update($task);

            $this->applicationManager->update($application);

            /**
             * clear dashboard table
             */
            $dashboards = $this->dashboardManager->getBy(['applicationId' => $id]);
            foreach ($dashboards as $dashboard) {
                $this->dashboardManager->delete($dashboard);
            }

            /**
             * remove acls
             */
            foreach ($this->userManager->getAll() as $user) {
                $acls = $user->removeAccessLevelsApp($id);
                $this->userManager->update($user);
                foreach ($acls as $acl) {
                    $this->accessLevelManager->delete($acl);
                }
            }

            /**
             * delete roles & tenants
             */
            foreach($this->environmentService->getAll() as $environment){
                $sharedIndex =  $environment->getName() . "-" . $application->getSharedIndex();
                $appIndex =  $environment->getName() . "-" . $application->getApplicationIndex();
                $watechrIndex = $environment->getName() ."-" . $application->getApplicationWatcherIndex();

                $this->es->deleteRole($environment->getName(), $application->getName(), true, false);
                $this->es->deleteRole($environment->getName(), $application->getName(), false, false);
                $this->es->deleteRole($environment->getName(), $application->getName(), true, true);

                $this->es->deleteTenant($sharedIndex);
                $this->es->deleteTenant($appIndex);
                $this->es->deleteTenant($watechrIndex);
            }

            /**
             * remove role mapping
             */
            foreach($this->environmentService->getAll() as $environment){
                $this->es->deleteRoleMapping($environment->getName(), $application->getName(), true, false);
                $this->es->deleteRoleMapping($environment->getName(), $application->getName(), false, false);
                $this->es->deleteRoleMapping($environment->getName(), $application->getName(), true, true);
            }
        }
    }

    public function removeUserApplication(string $id, User $user)
    {  
        try {

            $applicationPermission = $this->apManager->getOneBy(['application.id' => $id, 'user.id' => $user->getId()]);
            if ($applicationPermission !== null) {
                $application = $applicationPermission->getApplication();
                $applicationPermission->setAccess(ApplicationPermission::ACCESS_DENIED);
                $this->apManager->update($applicationPermission);
                $acls = $user->removeAccessLevelsApp($id);
                $this->userManager->update($user);
                foreach ($acls as $acl) {
                    $this->accessLevelManager->delete($acl);
                }
                /**
                 * remove role mapping
                 */
                foreach($this->environmentService->getAll() as $environment){
                    $this->es->updateRoleMapping("delete", $environment->getName(), $user, $application->getName(), true, false);
                    $this->es->updateRoleMapping("delete", $environment->getName(), $user, $application->getName(), false, false);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     *
     * @param string $id
     *
     * @return boolean
     */
    public function toggleActivation(string $id): bool
    {
        $isSuccessful = false;

        $application = $this->applicationManager->getOneBy(['id' => $id]);

        if ($application instanceof Application) {
            $application->toggleEnabled();
            $this->applicationManager->update($application);
            $isSuccessful = true;
        } else {
            throw new HttpException(Response::HTTP_NOT_FOUND, "Application not Found");
        }

        return $isSuccessful;
    }

    public function getActiveApplicationsNames()
    {
        return $this->applicationManager->getActiveApplicationsNames();
    }

    /**
     *
     * @param User $user
     *
     * @return void
     */
    public function registerDemoApplications(User $user): void
    {
        $demoApplications = $this->applicationManager->getBy(['demo' => true]);
        $env = $this->environmentService->getByName("staging");
        $now = new \DateTime();

        foreach ($demoApplications as $demoApplication) {
            $permission = $this->apManager->getOneBy(
                [
                    'application.id' => $demoApplication->getId(),
                    'user.id' => $user->getId(),
                ]
            );

            if ($permission instanceof ApplicationPermission) {
                continue;
            }

            $permission = new ApplicationPermission();
            $permission->setApplication($demoApplication)
                ->setUser($user)
                ->setAccess(ApplicationPermission::ACCESS_DEMO)
                ->setModifiedAt($now);
            $this->apManager->update($permission);

            $user->addAccessLevel((new AccessLevel())
                ->setEnvironment($env)
                ->setApplication($demoApplication)
                ->setLevel("ACCESS")
                ->setAccess("CONSULT"));

            $this->userManager->update($user);

        }
    }


    /**
     * Updates a specific app from JSON data
     *
     * @param string $dashboards
     * @param string $applicationId
     * @param string $userId
     *    
     * */
    public function updateApplicationDashboards($dashboards, $applicationId, $userId):array    {
      
        $state = [
            'successful' => false,
            'esUpdateRequired' => false,
            'application' => null,
        ];

        try {

            $array = json_decode($dashboards, true);
            $array_keys = array_keys( $array);

            $context = new DeserializationContext();
            $context->setGroups(['Default']);

            foreach ($array_keys as $value) {
                foreach ($array[$value] as $element) {
                   
                    $dashboard = $this->dashboardManager->getDashboard($userId, $applicationId, $element['id']);
                    $dashboard->setVisible($element['visible']);
                    $this->dashboardManager->update($dashboard);
                    $state['successful'] = true;
                }
            }

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $state['successful'] = false;
            $state['esUpdateRequired'] = false;
            $state['application'] = null;
        }

        return $state;
    }

    /**
     * Remove environment from application
     *
     * @param  Application $application
     * @param  Environment $environment
     *
     * @return Application
     */
    public function removeEnvironment(Application $application, Environment $environment)
    {
        $application->removeEnvironment($environment);
        $this->applicationManager->update($application);

        return $application;
    }

     /**
     * @param string $id
     */
    public function getById($id)
    {
        $application = $this->applicationManager->getOneBy(['id' => $id]);
        if ($application === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "Application not Found");
        } else {
            return $application;
        }

    }

    /**
     * @param string $name
     */
    public function getByName($name)
    {
        $application = $this->applicationManager->getOneBy(['name' => $name]);
        if ($application === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "Application not Found");
        } else {
            return $application;
        }

    }
}
