<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\User;
use AppBundle\Document\AccessLevel;
use AppBundle\Exception\DuplicateApplicationNameException;
use AppBundle\Manager\UserManager;
use AppBundle\Manager\AccessLevelManager;
use AppBundle\Service\ElasticSearchService;
use AppBundle\Service\EnvironmentService;
use AppBundle\Service\ApplicationService;
use AppBundle\Service\ProcessService;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Service class for AccessLevel entities
 *
 */
class AccessLevelService
{
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
    private $es;

    /**
     * @var EnvironmentService $environmentService
     */
    private $environmentService;

    /**
     * @var ApplicationService $applicationService
     */
    private $applicationService;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * Constructor
     *
     * @param UserManager         $userManager
     * @param AccessLevelManager  $accessLevelManager
     * @param ElasticSearchService  $es
     * @param EnvironmentService  $environmentService
     * @param ApplicationService  $applicationService
     * @param ProcessService      $processService
     * @param SerializerInterface $serializer
     * @param LoggerInterface     $logger
     */
    public function __construct(
        UserManager $userManager,
        AccessLevelManager $accessLevelManager,
        ElasticSearchService $es,
        EnvironmentService $environmentService,
        ApplicationService $applicationService,
        ProcessService $processService,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->userManager = $userManager;
        $this->accessLevelManager = $accessLevelManager;
        $this->es = $es;
        $this->environmentService = $environmentService;
        $this->applicationService = $applicationService;
        $this->processService = $processService;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * delete
     *
     * @param array $json
     *
     * @return User
     */
    public function delete($payload)
    {
        $env = $payload['env'];
        $app = $payload['app'];
        $level = $payload['level'];
        $user = $this->userManager->getOneBy(['id' => $payload['user']]);
        $environment = $this->environmentService->getById($env);
        $application = $this->applicationService->getById($app);
        $accessLevel = $user->getAccessLevelsApp($env, $app, $level);
        $user->removeAccessLevel($accessLevel);

        if($level === AccessLevel::REPORT){
            $this->es->updateRoleMapping("delete", $environment->getName(), $user, $application->getName(), true, true);
        } else {
            $this->es->updateRoleMapping("delete", $environment->getName(), $user, $application->getName(), true, false);
        }  

        $this->userManager->update($user);
        return $user;
    }

    /**
     * Update
     *
     * @param array $json
     *
     * @return User
     */
    public function update($payload)
    {
        try {
            $user = $this->userManager->getOneBy(['id' => $payload['user']]);
            if (!empty($payload['app']) && $payload['app'] != 'all') {
                $this->updateByApplication($user, $payload['env'], $payload['app'], $payload['level'], $payload['access']);
            } else {
                $this->updateByEnvironment($user, $payload['env'], $payload['level'], $payload['access']);
            }
        } catch (\Exception $e) {
            $this->processService->emit($user, "heavy-operations-done", "Failed");
            throw $e;
        }
        $this->processService->emit($user, "heavy-operations-done", "Successeded");
        return $user;
    }

    private function updateByEnvironment(User $user, $env, $level, $access)
    {
        $environment = $this->environmentService->getById($env);
        $accessLevels = $user->getAccessLevelsEnv($env, $level);
        foreach ($accessLevels as $accessLevel) {
            $user->removeAccessLevel($accessLevel);
            $accessLevel->setAccess($access);
            $user->addAccessLevel($accessLevel);
        }
        if (empty($accessLevels)) {
            foreach ($environment->getApplications() as $application) {
                if ($application->isRemoved()) {
                    continue;
                }
                $user->addAccessLevel((new AccessLevel())
                        ->setEnvironment($environment)
                        ->setApplication($application)
                        ->setLevel($level)
                        ->setAccess($access)
                    )
                ;
            }
        }
        $this->userManager->update($user);
    }

    private function updateByApplication(User $user, $env, $app, $level, $access)
    {
        $environment = $this->environmentService->getById($env);
        $application = $this->applicationService->getById($app);
        $accessLevel = $user->getAccessLevelsApp($env, $app, $level);
        if ($accessLevel == null) {
            $user->addAccessLevel((new AccessLevel())
                    ->setEnvironment($environment)
                    ->setApplication($application)
                    ->setLevel($level)
                    ->setAccess($access)
                );
        } else {
            $user->removeAccessLevel($accessLevel);
            $accessLevel->setAccess($access);
            $user->addAccessLevel($accessLevel);
        }
        if($level === AccessLevel::REPORT){
            if($access === AccessLevel::EDIT){
                $this->es->updateRoleMapping("add", $environment->getName(), $user, $application->getName(), true, true);
            } else {
                $this->es->updateRoleMapping("delete", $environment->getName(), $user, $application->getName(), true, true);
            } 
        } else {
            if($access === AccessLevel::EDIT){
                $this->es->updateRoleMapping("add", $environment->getName(), $user, $application->getName(), true, false);
            } else {
                $this->es->updateRoleMapping("delete", $environment->getName(), $user, $application->getName(), true, false);
            } 
        }
         

        $this->userManager->update($user);
    }

    /**
     * Get all accessLevels
     *
     * @return array
     */
    public function getAll()
    {
        return $this->accessLevelManager->getAll();
    }

    /**
     * bulk remove
     *
     * @param array $acls
     */
    public function removeBulk($acls)
    {
        foreach ($acls as $acl) {
            $this->accessLevelManager->delete($acl);
        }
    }

}
