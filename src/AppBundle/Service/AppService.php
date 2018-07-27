<?php declare(strict_types=1);

namespace AppBundle\Service;

use Psr\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use AppBundle\Manager\AppManager;
use AppBundle\Document\App;

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
     * Constructor
     *
     * @param AppManager $appManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(AppManager $appManager, SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->appManager = $appManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * List all apps
     *
     * @return array
     */
    public function listApps($userId)
    {
        return $this->appManager->getBy(['owner' => $userId]);
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
         return $this->appManager->getOneBy(['id' => $id]);
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
     * @return bool
     */
    public function newApp($json)
    {
        $app = $this
                ->serializer
                ->deserialize($json, App::class, 'json');

        return $this->updateApp($json);
    }

    /**
     * Updates a specific app from JSON data
     *
     * @param string $json
     *
     * @return bool
     */
    public function updateApp($json)
    {
        $isSuccessful = false;

        try {
            $app = $this->serializer->deserialize($json, App::class, 'json');
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
         $this->appManager->deleteById($id);
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
