<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\Application;
use AppBundle\Document\User;
use AppBundle\Manager\ActivationCodeManager;
use AppBundle\Manager\ApplicationManager;
use AppBundle\Manager\UserManager;
use AppBundle\Service\ActivationCodeService;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
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
     * @var UserManager
     */
    private $userManager;

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
        UserManager $userManager,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        LdapService $ldapService,
        KibanaService $kibana,
        ApplicationTypeService $appTypeService,
        ActivationCodeService $activationCodeService
    ) {
        $this->applicationManager = $applicationManager;
        $this->activationCodeManager = $activationCodeManager;
        $this->userManager = $userManager;
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

    public function listDemoApplications(): array
    {
        return $this->applicationManager->getBy(['demo' => true]);
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

    /**
     * @return void
     */
    public function createDemoApplications(): void
    {
        $demoAppOwner = $this->userManager->getOneBy(['username' => 'demoAppUser']);
        $applicationType = $this->appTypeService->getApplicationTypes(['name' => 'Java'])[0];
        if ($demoAppOwner === null) {
            $demoAppOwner = $this->userManager->create("demoAppUser", Uuid::uuid1()->toString(), '', 'dempAppUser');
        }

        $app = $this->applicationManager->getOneBy(['uuid' => "a16274f8-dbd2-11e8-b444-fa163e30b6da"]);

        if ($app === null) {
            $app = new Application();
            $app->setUuid("a16274f8-dbd2-11e8-b444-fa163e30b6da") // * UUID has to be hardcoded since it will be used on Kibana and stuff
                ->setName("jpetstore")
                ->setDescription("A web application built on top of MyBatis 3, Spring 3 and Stripes")
                ->setEmail("wassim.dhib@leadwire.io")
                ->setEnabled(true)
                ->setDemo(true)
                ->setRemoved(false)
                ->setOwner($demoAppOwner)
                ->setType($applicationType);

            $this->applicationManager->update($app);
        }

        $app = $this->applicationManager->getOneBy(['uuid' => "f007bb9a-dbd2-11e8-87b3-fa163e30b6da"]);

        if ($app === null) {
            $app = new Application();
            $app->setUuid("f007bb9a-dbd2-11e8-87b3-fa163e30b6da") // * UUID has to be hardcoded since it will be used on Kibana and stuff
                ->setName("squash")
                ->setDescription("Squash TM est un outil open source de gestion de référentiels de tests : gestion des exigences, cas de test, campagnes, etc. Squash est full web et nativement inter-projets.")
                ->setEmail("wassim.dhib@leadwire.io")
                ->setEnabled(true)
                ->setDemo(true)
                ->setRemoved(false)
                ->setOwner($demoAppOwner)
                ->setType($applicationType);

            $this->applicationManager->update($app);
        }
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

        foreach ($demoApplications as $demoApplication) {
            $user->addApplication($demoApplication);
        }

        $this->userManager->update($user);
    }
}
