<?php declare(strict_types=1);

namespace AppBundle\Service;

use Psr\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use AppBundle\Manager\StatManager;
use AppBundle\Document\Stat;

/**
 * Service class for Stat entities
 *
 */
class StatService
{
    /**
     * @var StatManager
     */
    private $statManager;

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
     * @param StatManager $statManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(StatManager $statManager, SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->statManager = $statManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * List all stats
     *
     * @return array
     */
    public function listStats()
    {
        return $this->statManager->getAll();
    }

    /**
     * Paginates through Stats
     *
     * @param int $pageNumber
     * @param int $itemsPerPage
     * @param array $criteria
     *
     * @return array
     */
    public function paginate($pageNumber = 1, $itemsPerPage = 20, array $criteria = [])
    {
        return $this->statManager->paginate($criteria, $pageNumber, $itemsPerPage);
    }

    /**
     * Get a specific stat
     *
     * @param string $id
     *
     * @return Stat
     */
    public function getStat($id)
    {
         return $this->statManager->getOneBy(['id' => $id]);
    }

    /**
     * Get specific stats
     *
     * @param string $criteria
     *
     * @return array
     */
    public function getStats(array $criteria = [])
    {
         return $this->statManager->getBy($criteria, array("day" => "DESC"), 15);
    }

    /**
     * Creates a new stat from JSON data
     *
     * @param string $json
     *
     * @return bool
     */
    public function newStat($json)
    {
        $stat = $this
                ->serializer
                ->deserialize($json, Stat::class, 'json');

        return $this->updateStat($json);
    }

    /**
     * Updates a specific stat from JSON data
     *
     * @param string $json
     *
     * @return bool
     */
    public function updateStat($json)
    {
        $isSuccessful = false;

        try {
            $stat = $this->serializer->deserialize($json, Stat::class, 'json');
            $this->statManager->update($stat);
            $isSuccessful = true;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $isSuccessful = false;
        }

        return $isSuccessful;
    }

    /**
     * Deletes a specific stat from JSON data
     *
     * @param string $id
     *
     * @return void
     */
    public function deleteStat($id)
    {
         $this->statManager->deleteById($id);
    }

     /**
      * Performs a full text search on  Stat
      *
      * @param string $term
      * @param string $lang
      *
      * @return array
      */
    public function textSearch($term, $lang)
    {
        return $this->statManager->textSearch($term, $lang);
    }

    /**
     * Performs multi-field grouped query on Stat
     * @param array $searchCriteria
     * @param string $groupField
     * @param \Closure $groupValueProcessor
     * @return array
     */
    public function getAndGroupBy(array $searchCriteria, $groupFields = [], $valueProcessors = [])
    {
        return $this->statManager->getAndGroupBy($searchCriteria, $groupFields, $valueProcessors);
    }
}
