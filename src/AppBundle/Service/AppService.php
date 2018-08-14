<?php declare(strict_types=1);

namespace AppBundle\Service;

use AppBundle\Document\User;
use JMS\Serializer\DeserializationContext;
use Psr\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use AppBundle\Manager\AppManager;
use AppBundle\Document\App;
use Ramsey\Uuid\Uuid;

/**
 * Service class for App entities
 *
 */
class AppService
{
    /**
     * @var AppManager
     */
    private $appManager;

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
     * @var Kibana
     */
    private $kibana;

    /**
     * @var ApplicationTypeService
     */
    private $apService;

    /**
     * Constructor
     *
     * @param AppManager $appManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @param LdapService $ldapService
     * @param Kibana $kibana
     * @param ApplicationTypeService $apService
     */
    public function __construct(
        AppManager $appManager,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        LdapService $ldapService,
        Kibana $kibana,
        ApplicationTypeService $apService
    ) {
        $this->appManager = $appManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->ldapService = $ldapService;
        $this->kibana = $kibana;
        $this->apService = $apService;
    }

    /**
     * List all apps
     *
     * @param User $user
     * @return array
     */
    public function listApps(User $user)
    {
        return $this->appManager->getBy(['owner' => $user, 'isRemoved' => false]);
    }

    public function invitedListApps(User $user)
    {
        $apps = [];
        foreach ($user->invitations as $invitation) {
            $app = $invitation->getApp();
            if (!$app->getIsRemoved()) {
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
        return $this->appManager->paginate($criteria, $pageNumber, $itemsPerPage);
    }

    /**
     * Get a specific app
     *
     * @param string $id
     *
     * @return App
     */
    public function getApp($id)
    {
        return $this->appManager->getOneBy(['_id' => $id, 'isRemoved' => false]);
    }

    /**
     * Get specific apps
     *
     * @param string $criteria
     *
     * @return array
     */
    public function getApps(array $criteria = [])
    {
        return $this->appManager->getBy($criteria);
    }

    /**
     * Creates a new app from JSON data
     *
     * @param string $json
     *
     * @param User $user
     * @return App
     * @throws \Exception
     */
    public function newApp($json, User $user)
    {
        $context = new DeserializationContext();
        $context->setGroups(['Default']);
        $app = $this
            ->serializer
            ->deserialize($json, App::class, 'json', $context);

        $app->setOwner($user);
        $uuid1 = Uuid::uuid1();
        $app->setUuid($uuid1->toString());
        $ap = $this->apService->getApplicationType($app->getType()->getId());
        $app->setType($ap);
        $this->appManager->update($app);
        if ($this->ldapService->createLdapAppEntry($user->getUuid(), $app->getName()) &&
            $this->kibana->createDashboards($app)) {
            return $app;
        } else {
            $this->appManager->delete($app);
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
            $realApp = $this->appManager->getOneBy(['id' => $id]);
            if (!$realApp) {
                return false;
            }
            $context = new DeserializationContext();
            $context->setGroups(['Default']);
            $app = $this->serializer->deserialize($json, App::class, 'json', $context);
            $this->appManager->update($app);
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
        $this->appManager->update($this->appManager->getOneBy(['id' => $id])->setIsRemoved(true));
    }

    /**
     * Performs a full text search on  App
     *
     * @param string $term
     * @param string $lang
     *
     * @return array
     */
    public function textSearch($term, $lang)
    {
        return $this->appManager->textSearch($term, $lang);
    }

    /**
     * Performs multi-field grouped query on App
     * @param array $searchCriteria
     * @param string $groupField
     * @param \Closure $groupValueProcessor
     * @return array
     */
    public function getAndGroupBy(array $searchCriteria, $groupFields = [], $valueProcessors = [])
    {
        return $this->appManager->getAndGroupBy($searchCriteria, $groupFields, $valueProcessors);
    }
}
