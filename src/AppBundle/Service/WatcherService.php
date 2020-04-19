<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\Watcher;
use AppBundle\Exception\DuplicateApplicationNameException;
use AppBundle\Manager\WatcherManager;
use AppBundle\Service\ElasticSearchService;

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
     * @var ElasticSearchService
     */
    private $es;

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
     * @param WatcherManager        $watcherManager
     * @param ElasticSearchService  $es
     * @param SerializerInterface   $serializer
     * @param LoggerInterface       $logger
     */
    public function __construct(
        WatcherManager $watcherManager,
        ElasticSearchService $es,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->watcherManager = $watcherManager;
        $this->es = $es;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }


    /**
     * add
     *
     * @param array $json
     *
     * @return Watcher
     */
    public function add($json) {
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
        
        if ($db !== null) {
            throw new DuplicateApplicationNameException("An watcher with the same title already exists");
        }else {
            $id = $this->watcherManager->update($watcher);
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

}
