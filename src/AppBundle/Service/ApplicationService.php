<?php declare (strict_types = 1);

namespace AppBundle\Service;

use Ramsey\Uuid\Uuid;
use AppBundle\Document\User;
use Psr\Log\LoggerInterface;
use AppBundle\Document\Application;
use JMS\Serializer\SerializerInterface;
use AppBundle\Manager\ApplicationManager;
use JMS\Serializer\DeserializationContext;
use AppBundle\Manager\ActivationCodeManager;
use AppBundle\Service\ActivationCodeService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
     * @var ActivationCodeManager
    */
    private $activationCodeManager;

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
    private $appTypeService;

    /**
     * @var ActivationCodeService
     */
    private $activationCodeService;

    /**
     * Constructor
     *
     * @param ApplicationManager $applicationManager
     * @param ActivationCodeManager $activationCodeManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @param LdapService $ldapService
     * @param KibanaService $kibana
     * @param ApplicationTypeService $appTypeService
     * @param ActivationCodeService $activationCodeService
     */
    public function __construct(
        ApplicationManager $applicationManager,
        ActivationCodeManager $activationCodeManager,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        LdapService $ldapService,
        KibanaService $kibana,
        ApplicationTypeService $appTypeService,
        ActivationCodeService $activationCodeService
    ) {
        $this->applicationManager = $applicationManager;
        $this->activationCodeManager = $activationCodeManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->ldapService = $ldapService;
        $this->kibana = $kibana;
        $this->appTypeService = $appTypeService;
        $this->activationCodeService = $activationCodeService;
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
        $apps = [];

        foreach ($user->invitations as $invitation) {
            $app = $invitation->getApplication();
            if ($app->isRemoved() !== false) {
                $apps[] = $app;
            }
        }

        return $apps;
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
        $app = $this->getApplication($id);

        if ($activationCode !== null && $app !== null) {
            $valid = $this->activationCodeService->validateActivationCode($activationCode);

            if ($valid === true) {
                $app->setEnabled(true);
                $activationCode->setApplication($app);
                $activationCode->setUsed(true);
                $this->applicationManager->update($app);
                $this->activationCodeManager->update($activationCode);
                $result = $app;
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
        $ap = $this->appTypeService->getApplicationType($applicationTypeId);
        $app->setType($ap);
        $this->applicationManager->update($app);

        $ldapEntryCreationStatus = $this->ldapService->createAppEntry($user->getIndex(), $app->getUuid());

        // !Dashboard creation is bogus
        $dashboardsCreationStatus = true; //$this->kibana->createDashboards($app);

        if ($ldapEntryCreationStatus === true && $dashboardsCreationStatus === true) {
            return $app;
        } else {
            $this->applicationManager->delete($app);
            $this->logger->critical("Application was removed due to error in Ldap/Kibana or Elastic search");

            return null;
        }

        return $app;
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
            if ($realApp === null) {
                throw new \Exception(sprintf("Unknow application %s", $id));
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
        $application = $this->applicationManager->getOneBy(['id' => $id]);
        if ($application === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "Application not Found");
        } else {
            $application->setRemoved(true);

            $this->applicationManager->update($application);
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
}
