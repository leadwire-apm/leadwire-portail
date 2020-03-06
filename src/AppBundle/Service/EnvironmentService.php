<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\Application;
use AppBundle\Document\Environment;
use AppBundle\Exception\DuplicateApplicationNameException;
use AppBundle\Manager\EnvironmentManager;
use AppBundle\Manager\ApplicationManager;
use AppBundle\Service\SearchGuardService;
use AppBundle\Manager\UserManager;
use AppBundle\Manager\AccessLevelManager;
use AppBundle\Document\AccessLevel;
use AppBundle\Service\ElasticSearchService;
use AppBundle\Service\KibanaService;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;



/**
 * Service class for Environment entities
 *
 */
class EnvironmentService
{
    /**
     * @var EnvironmentManager
     */
    private $environmentManager;

    /**
     * @var ApplicationManager
     */
    private $applicationManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SearchGuardService
     */
    private $searchGuardService;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var AccessLevelManager
     */
    private $accessLevelManager;

    /**
     * @var ElasticSearchService
     */
    private $elasticSearchService;

    /**
     * @var KibanaService
     */


    /**
     * Constructor
     *
     * @param EnvironmentManager $environmentManager
     * @param ApplicationManager $applicationManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @param SearchGuardService $searchGuardService
     * @param UserManager $userManager
     * @param AccessLevelManager $accessLevelManager
     * @param ElasticSearchService $elasticSearchService
     * @param KibanaService $kibanaService
     */
    public function __construct(
        EnvironmentManager $environmentManager,
        ApplicationManager $applicationManager,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        SearchGuardService $searchGuardService,
        UserManager $userManager,
        AccessLevelManager $accessLevelManager,
        ElasticSearchService $elasticSearchService,
        KibanaService $kibanaService
        ) {
        $this->environmentManager = $environmentManager;
        $this->applicationManager = $applicationManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->searchGuardService = $searchGuardService;
        $this->userManager = $userManager;
        $this->accessLevelManager = $accessLevelManager;
        $this->es = $elasticSearchService;
        $this->kibanaService = $kibanaService;
    }


    public function list()
    {
        $environments = $this->environmentManager->getAll();

        return $environments;
    }

    public function add($json)
    {
        $environment = $this
            ->serializer
            ->deserialize($json, Environment::class, 'json');
        if (count($this->getAll()) == 0) {
            $environment->setDefault(true);
        }

        $id = $this->environmentManager->update($environment);

        $env = $this->getById($id);

        /**
         * Add applications
         */
        foreach ($this->applicationManager->getActiveApplicationsNames() as $application) {
            $application->addEnvironment($env);
            $this->applicationManager->update($application);

            //Add applications tenants
            $envName = $env->getName();
            $sharedIndex =  $envName . "-" . $application->getSharedIndex();
            $appIndex =  $envName . "-" . $application->getApplicationIndex();
            $patternIndex = "*-" . $envName . "-" . $application->getName() . "-*";
            
            if($application->isRemoved() === false){
                $this->es->createTenant($appIndex);

                $this->es->createIndexTemplate($application, $this->applicationManager->getActiveApplicationsNames());
        
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
    
                $this->kibanaService->loadDefaultIndex($sharedIndex, 'default');
                $this->kibanaService->makeDefaultIndex($sharedIndex, 'default');

                $this->es->createRole($envName, $application->getName(), array($patternIndex), array($sharedIndex, $appIndex), array("kibana_all_read"), false);
                $this->es->createRole($envName, $application->getName(), array($patternIndex), array($sharedIndex, $appIndex), array("kibana_all_write"), true);

                $this->es->createRoleMapping($envName, $application->getName(), '', array('read'), false);
                $this->es->createRoleMapping($envName, $application->getName(), '', array('write'), true);
            }
        }

        /**
         * create  acls
         */
        foreach ($this->userManager->getAll() as $user) {
            $acls = $user->getAccessLevels();
            foreach ($acls as $acl) {
                $user->addAccessLevel((new AccessLevel())
                        ->setEnvironment($env)
                        ->setApplication($acl->getApplication())
                        ->setLevel($acl->getLevel())
                        ->setAccess($acl->getAccess())
                    );

                $this->es->updateRoleMapping("add", $env->getName(), $user, $acl->getApplication()->getName(), false);

                if($acl->getAccess() === AccessLevel::WRITE_ACCESS){
                    $this->es->updateRoleMapping("add", $env->getName(), $user, $acl->getApplication()->getName(), true);
                }
            }
            $this->userManager->update($user);
        }
        return $id;
    }

    public function update($json)
    {
        $context = new DeserializationContext();
        $context->setGroups(['minimalist']);
        $environment = $this->serializer->deserialize($json, Environment::class, 'json', $context);
        $this->environmentManager->update($environment);
        $this->searchGuardService->updateSearchGuardConfig();
    }

    /**
     * @param string $id
     */
    public function delete($id)
    {
        $environment = $this->environmentManager->getOneBy(['id' => $id]);
        if ($environment === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "Environment not Found");
        } else {
            foreach ($environment->getApplications() as $application) {
                $application->removeEnvironment($environment);
                $this->applicationManager->update($application);
            }

            /**
             * remove acls
             */
            foreach ($this->userManager->getAll() as $user) {
                $acls = $user->removeAccessLevelsEnv($id);
                $this->userManager->update($user);
                foreach ($acls as $acl) {
                    $this->accessLevelManager->delete($acl);
                }
            }

            /**
             * delete tenant
             */
            foreach ($environment->getApplications() as $application) {
                $sharedIndex =  $environment->getName() . "-" . $application->getSharedIndex();
                $appIndex =  $environment->getName() . "-" . $application->getApplicationIndex();
                $this->es->deleteRole($environment->getName(), $application->getName(), true);
                $this->es->deleteRole($environment->getName(), $application->getName(), false);

                $this->es->deleteRoleMapping($environment->getName(), $application->getName(), true);
                $this->es->deleteRoleMapping($environment->getName(), $application->getName(), false);

                $this->es->deleteTenant($sharedIndex);
                $this->es->deleteTenant($appIndex);
            }
            return $this->environmentManager->delete($environment);
        }

    }

     /**
     * @param string $id
     */
    public function getById($id)
    {
        $environment = $this->environmentManager->getOneBy(['id' => $id]);
        if ($environment === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "Environment not Found");
        } else {
            return $environment;
        }

    }

    /**
     * @param string $name
     */
    public function getByName($name)
    {

        $environment = $this->environmentManager->getOneBy(['name' => $name]);
        if ($environment === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "Environment not Found");
        } else {
            return $environment;
        }

    }

    /**
     * Get default env
     *
     * @return Environment
     */
    public function getDefault()
    {
        $environment = $this->environmentManager->getOneBy(['default' => true]);

        return $environment;
    }

    /**
     * Set default env
     *
     * @param string $id
     *
     * @return Environment
     */
    public function setDefault($id)
    {
        $environments = $this->environmentManager->getAll();
        foreach ($environments as $environment) {
            $environment->setDefault(false);
            if ((string)$environment->getId() == $id) {
                $environment->setDefault(true);
            }
            $this->environmentManager->update($environment);
        }

        $environment = $this->environmentManager->getOneBy(['default' => true]);
        if ($environment === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "Environment not Found");
        } else {
            return $environment;
        }

    }

    /**
     * Get all environments
     *
     * @return array
     */
    public function getAll()
    {
        return $this->environmentManager->getAll();
    }

}
