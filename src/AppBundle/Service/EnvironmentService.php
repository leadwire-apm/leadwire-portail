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
     * Constructor
     *
     * @param EnvironmentManager $environmentManager
     * @param ApplicationManager $applicationManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @param SearchGuardService $searchGuardService
     * @param UserManager $userManager
     * @param AccessLevelManager $accessLevelManager
     */
    public function __construct(
        EnvironmentManager $environmentManager,
        ApplicationManager $applicationManager,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        SearchGuardService $searchGuardService,
        UserManager $userManager,
        AccessLevelManager $accessLevelManager
    ) {
        $this->environmentManager = $environmentManager;
        $this->applicationManager = $applicationManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->searchGuardService = $searchGuardService;
        $this->userManager = $userManager;
        $this->accessLevelManager = $accessLevelManager;
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
        foreach ($this->applicationManager->getAll() as $application) {
            $application->addEnvironment($env);
            $this->applicationManager->update($application);
        }

        /**
         * create  acls
         */
        foreach ($this->userManager->getAll() as $user) {
            $acls = $user->getAccessLevels();
            foreach ($acls as $acl) {
                $user
                    ->addAccessLevel((new AccessLevel())
                        ->setEnvironment($env)
                        ->setApplication($acl->getApplication())
                        ->setLevel($acl->getLevel())
                        ->setAccess($acl->getAccess())
                    )
                ;
            }
            $this->userManager->update($user);
        }

        $this->searchGuardService->updateSearchGuardConfig();

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

            $this->searchGuardService->updateSearchGuardConfig();
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
