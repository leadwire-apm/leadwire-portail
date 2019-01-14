<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\Application;
use AppBundle\Document\User;
use AppBundle\Manager\ApplicationManager;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var LdapService
     */
    private $ldapService;

    /**
     * @var KibanaService
     */
    private $kibana;

    /**
     * @var ApplicationTypeService
     */
    private $apService;

    /**
     * @var ElasticSearchService
     */
    private $elastic;
    /**
     * Constructor
     *
     * @param ApplicationManager $applicationManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @param LdapService $ldapService
     * @param KibanaService $kibana
     * @param ApplicationTypeService $apService
     * @param ElasticSearchService $elastic
     */
    public function __construct(
        ApplicationManager $applicationManager,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        LdapService $ldapService,
        KibanaService $kibana,
        ApplicationTypeService $apService,
        ElasticSearchService $elastic
    ) {
        $this->applicationManager = $applicationManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->ldapService = $ldapService;
        $this->kibana = $kibana;
        $this->apService = $apService;
        $this->elastic = $elastic;
    }

    /**
     * List all apps
     *
     * @param User $user
     *
     * @return array
     */
    public function listApps(User $user)
    {
        return $this->applicationManager->getBy(['owner' => $user, 'removed' => false]);
    }

    /**
     * @var USer $user
     *
     * @return array
     */
    public function invitedListApps(User $user)
    {
        $apps = [];

        foreach ($user->invitations as $invitation) {
            $app = $invitation->getApplication();
            if ($app->isRemoved() === false) {
                $apps[] = $app;
            }
        }
        return $apps;
    }

    /**
     * Paginates through Apps
     *
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
        return $this->applicationManager->getOneBy(['_id' => $id, 'removed' => false]);
    }

    /**
     * Activate Disabled App
     *
     * @param string $id
     * @param \stdClass $body
     *
     * @return ?Application
     */
    public function activateApplication($id, $body): ?Application
    {
        $code = $body->code;

        if ((strlen($code) === 6) && (substr($code, 1, 1) === 'B') &&
            (substr($code, 4, 1) === '7') &&
            (strtoupper($code) === $code)) {
            $app = $this->getApplication($id);

            if ($app instanceof Application) {
                $app->setEnabled(true);
            } else {
                throw new \Exception(sprintf("Unknow application %s", $id));
            }

            $this->applicationManager->update($app);

            return $app;
        } else {
            return null;
        }
    }

    /**
     * Get specific apps
     *
     * @param array $criteria
     *
     * @return array
     */
    public function getApps(array $criteria = [])
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
    public function newApp($json, User $user): ?Application
    {
        $context = new DeserializationContext();
        $context->setGroups(['Default']);
        /** @var Application $app */
        $app = $this
            ->serializer
            ->deserialize($json, Application::class, 'json', $context);

        $uuid1 = Uuid::uuid1();
        $app
            ->setOwner($user)
            ->setEnabled(false)
            ->setUuid($uuid1->toString())
            ->setRemoved(false);

        /** @var string $applicationTypeId */
        $applicationTypeId = $app->getType()->getId();
        $ap = $this->apService->getApplicationType($applicationTypeId);
        $app->setType($ap);
        $this->applicationManager->update($app);
        if ($this->ldapService->createAppEntry($user->getIndex(), $app->getUuid()) === true &&
            $this->kibana->createDashboards($app) === true) {
            return $app;
        } else {
            $this->applicationManager->delete($app);
            $this->logger->critical("Application was removed due to error in Ldap/Kibana or Elastic search");

            return null;
        }
    }

    /**
     * Updates a specific app from JSON data
     *
     * @param string $json
     *
     * @return bool
     */
    public function updateApp($json, $id)
    {
        $isSuccessful = false;

        try {
            $realApp = $this->applicationManager->getOneBy(['id' => $id]);
            if ($realApp !== null) {
                return false;
            }
            $context = new DeserializationContext();
            $context->setGroups(['Default']);
            $app = $this->serializer->deserialize($json, Application::class, 'json', $context);
            $this->applicationManager->update($app);
            $isSuccessful = true;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $isSuccessful = false;
        }

        return $isSuccessful;
    }

    /**
     * Deletes a specific app from JSON data
     *
     * @param string $id
     *
     * @return void
     */
    public function deleteApp($id)
    {
        $this->applicationManager->update($this->applicationManager->getOneBy(['id' => $id])->setRemoved(true));
    }
}
