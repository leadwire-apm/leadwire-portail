<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\Watcher;
use AppBundle\Exception\DuplicateApplicationNameException;
use AppBundle\Manager\UserManager;
use AppBundle\Manager\WatcherManager;
use AppBundle\Service\ElasticSearchService;
use AppBundle\Service\ProcessService;

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
     * @param ProcessService        $processService
     * @param SerializerInterface   $serializer
     * @param LoggerInterface       $logger
     */
    public function __construct(
        WatcherManager $watcherManager,
        ElasticSearchService $es,
        ProcessService $processService,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->watcherManager = $watcherManager;
        $this->es = $es;
        $this->processService = $processService;
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
    public function add($payload)
    {

    }



    /**
     * Get all watchers
     *
     * @return array
     */
    public function getByAppId()
    {
        return $this->watcherManager->getAll();
    }

}
