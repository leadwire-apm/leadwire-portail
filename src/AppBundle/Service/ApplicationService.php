<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\Application;
use AppBundle\Document\ApplicationPermission;
use AppBundle\Document\DeleteTask;
use AppBundle\Document\Task;
use AppBundle\Document\User;
use AppBundle\Exception\DuplicateApplicationNameException;
use AppBundle\Manager\ActivationCodeManager;
use AppBundle\Manager\ApplicationManager;
use AppBundle\Manager\ApplicationPermissionManager;
use AppBundle\Manager\DeleteTaskManager;
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
     * Constructor
     *
     * @param ApplicationManager $applicationManager
     * @param ActivationCodeManager $activationCodeManager
     * @param DeleteTaskManager $taskManager
     * @param ApplicationPermissionManager $apManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @param ApplicationTypeService $appTypeService
     * @param ActivationCodeService $activationCodeService
     */
    public function __construct(
        ApplicationManager $applicationManager,
        ActivationCodeManager $activationCodeManager,
        DeleteTaskManager $taskManager,
        ApplicationPermissionManager $apManager,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        ApplicationTypeService $appTypeService,
        ActivationCodeService $activationCodeService
    ) {
        $this->applicationManager = $applicationManager;
        $this->activationCodeManager = $activationCodeManager;
        $this->taskManager = $taskManager;
        $this->apManager = $apManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
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

        $dbApplication = $this->applicationManager->getOneBy(['name' => $application->getName()]);

        if ($dbApplication !== null) {
            throw new DuplicateApplicationNameException("An application with the same name already exists");
        }

        $uuid1 = Uuid::uuid1();
        $application
            ->setOwner($user)
            ->setEnabled(false)
            ->setUuid($uuid1->toString())
            ->setRemoved(false);

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

        return $application;
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
            $application = $this->serializer->deserialize($json, Application::class, 'json', $context);
            $this->applicationManager->update($application);
            $isSuccessful = true;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $isSuccessful = false;
        }

        return $isSuccessful;
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
        }
    }

    public function removeUserApplication(string $id, User $user)
    {
        $applicationPermission = $this->apManager->getOneBy(['application.id' => $id, 'user.id' => $user->getId()]);

        if ($applicationPermission !== null) {
            $applicationPermission->setAccess(ApplicationPermission::ACCESS_DENIED);
            $this->apManager->update($applicationPermission);
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
        $now = new \DateTime();

        foreach ($demoApplications as $demoApplication) {
            $permission = $this->apManager->getOneBy(
                [
                    'application.id' => $demoApplication->getId(),
                    'user.id' => $user->getId()
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
        }
    }
}
