<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\Watcher;
use AppBundle\Exception\DuplicateApplicationNameException;
use AppBundle\Manager\WatcherManager;
use AppBundle\Service\KibanaService;
use AppBundle\Service\EnvironmentService;
use AppBundle\Service\ApplicationService;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Service class for WatcherService entities
 *
 */
class WatcherService
{

    /**
     * @var WatcherManager
     */
    private $watcherManager;

    /**
     * @var KibanaService
     */
    private $KibanaService;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EnvironmentService $environmentService
     */
    private $environmentService;

    /**
     * @var ApplicationService $applicationService
     */
    private $applicationService;


    /**
     * Constructor
     *
     * @param WatcherManager        $watcherManager
     * @param KibanaService         $KibanaService
     * @param SerializerInterface   $serializer
     * @param LoggerInterface       $logger
     * @param EnvironmentService  $environmentService
     * @param ApplicationService  $applicationService
     */
    public function __construct(
        WatcherManager $watcherManager,
        KibanaService $KibanaService,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        EnvironmentService $environmentService,
        ApplicationService $applicationService
    ) {
        $this->watcherManager = $watcherManager;
        $this->KibanaService = $KibanaService;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->environmentService = $environmentService;
        $this->applicationService = $applicationService;
    }


    /**
     * add
     *
     * @param array $json
     *
     * @return Watcher
     */
    public function saveOrUpdate($json) {
        $context = new DeserializationContext();
        $context->setGroups(['Default']);
        /** @var Watcher $watcher */
        $watcher = $this
            ->serializer
            ->deserialize($json, Watcher::class, 'json', $context);
        
        $db = $this->watcherManager->getOneBy(
            ['titre' => $watcher->getTitre(),
             'appId' => $watcher->getAppId(),
             'envId' => $watcher->getEnvId() ]);
        
        if ($db !== null && !$watcher->getId()) {
            throw new DuplicateApplicationNameException("An watcher with the same title already exists");
        }else {
            $id = $this->watcherManager->update($watcher);
            $environment = $this->environmentService->getById($watcher->getEnvId());
            $application = $this->applicationService->getById($watcher->getAppId());
            $watechrIndex = $environment->getName() . "-" . $application->getApplicationWatcherIndex();
            $this->KibanaService->createWatcher($watcher, $watechrIndex);
        }

        return $id;
    }

    /**
     * Get all watchers
     *
     * @return array
     */
    public function list($payload) {
        return $this->watcherManager->getByEnvDash( $payload['appId'], $payload['envId']);
    }

    /**
     * delete watcher
     *
     * @return array
     */
    public function delete($id) {
        $watcher = $this->watcherManager->getOneBy(['id' => $id]);
        if ($watcher === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "Watcher not Found");
        } else {
            return $this->watcherManager->delete($watcher);
        }
    }

}
