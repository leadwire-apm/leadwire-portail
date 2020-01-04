<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\User;
use AppBundle\Document\AccessLevel;
use AppBundle\Exception\DuplicateApplicationNameException;
use AppBundle\Manager\UserManager;
use AppBundle\Manager\AccessLevelManager;
use AppBundle\Service\SearchGuardService;
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
     * @var SearchGuardService
     */
    private $searchGuardService;

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
     * @param SearchGuardService  $searchGuardService
     * @param ProcessService      $processService
     * @param SerializerInterface $serializer
     * @param LoggerInterface     $logger
     */
    public function __construct(
        UserManager $userManager,
        AccessLevelManager $accessLevelManager,
        SearchGuardService $searchGuardService,
        ProcessService $processService,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->userManager = $userManager;
        $this->accessLevelManager = $accessLevelManager;
        $this->searchGuardService = $searchGuardService;
        $this->processService = $processService;
        $this->serializer = $serializer;
        $this->logger = $logger;
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
            $this->processService->emit("heavy-operations-in-progress", "Updating SearchGuard Configuration");
            $user = $this->userManager->getOneBy(['id' => $payload['user']]);
            if (isset($payload['app'])) {
                $this->updateByApplication($user, $payload['env'], $payload['app'], $payload['level'], $payload['access']);
            } else {
                $this->updateByEnvironment($user, $payload['env'], $payload['level'], $payload['access']);
            }
        } catch (\Exception $e) {
            $this->processService->emit("heavy-operations-done", "Failed");
            throw $e;
        }
        $this->processService->emit("heavy-operations-done", "Successeded");

        return $user;
    }

    private function updateByEnvironment(User $user, $env, $level, $access)
    {
        foreach ($user->getAccessLevels() as $accessLevel) {
            if (
                $env == (string) $accessLevel->getEnvironment()->getId()
                && $level == $accessLevel->getLevel()
            ) {
                $accessLevel->setAccess($access);
                $this->accessLevelManager->update($accessLevel);
                $this->searchGuardService->updateSearchGuardConfig();
            }
        }
    }

    private function updateByApplication(User $user, $env, $app, $level, $access)
    {
        $acl = null;
        foreach ($user->getAccessLevels() as $accessLevel) {
            if (
                $env == (string) $accessLevel->getEnvironment()->getId()
                && $app == (string) $accessLevel->getApplication()->getId()
                && $level == $accessLevel->getLevel()
            ) {
                $accessLevel->setAccess($access);
                $this->accessLevelManager->update($accessLevel);
                $this->searchGuardService->updateSearchGuardConfig();
            }
        }
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

}
