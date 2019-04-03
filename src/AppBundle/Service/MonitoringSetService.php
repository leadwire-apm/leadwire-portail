<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\MonitoringSet;
use AppBundle\Manager\MonitoringSetManager;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;

/**
 * Service class for MonitoringSet entities
 *
 */
class MonitoringSetService
{
    /**
     * @var MonitoringSetManager
     */
    private $monitoringSetManager;

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
     * @param MonitoringSetManager $monitoringSetManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        MonitoringSetManager $monitoringSetManager,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->monitoringSetManager = $monitoringSetManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * List all MonitoringSets
     * @codeCoverageIgnore
     *
     * @return array
     */
    public function listMonitoringSets()
    {
        return $this->monitoringSetManager->getAll();
    }

    /**
     * Paginates through MonitoringSets
     *
     * @codeCoverageIgnore
     *
     * @param int $pageNumber
     * @param int $itemsPerPage
     * @param array $criteria
     *
     * @return array
     */
    public function paginate($pageNumber = 1, $itemsPerPage = 20, array $criteria = [])
    {
        return $this->monitoringSetManager->paginate($criteria, $pageNumber, $itemsPerPage);
    }

    /**
     * Get a specific MonitoringSet
     *
     * @codeCoverageIgnore
     * @param string $id
     *
     * @return MonitoringSet
     */
    public function getMonitoringSet($id)
    {
        return $this->monitoringSetManager->getOneBy(['id' => $id]);
    }

    /**
     * Get specific MonitoringSets
     *
     * @codeCoverageIgnore
     * @param array $criteria
     *
     * @return array
     */
    public function getMonitoringSets(array $criteria = [])
    {
        return $this->monitoringSetManager->getBy($criteria);
    }

    /**
     * Creates a new MonitoringSet from JSON data
     *
     * @codeCoverageIgnore
     * @param string $json
     *
     * @return bool
     */
    public function newMonitoringSet($json)
    {
        $monitoringSet = $this->serializer->deserialize($json, MonitoringSet::class, 'json');
        $this->monitoringSetManager->update($monitoringSet);

        return true;
    }

    /**
     * Updates a specific MonitoringSet from JSON data
     *
     * @codeCoverageIgnore
     *
     * @param string $json
     *
     * @return bool
     */
    public function updateMonitoringSet($json)
    {
        $isSuccessful = false;

        try {
            $monitoringSet = $this->serializer->deserialize($json, MonitoringSet::class, 'json');
            $this->monitoringSetManager->update($monitoringSet);
            $isSuccessful = true;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $isSuccessful;
    }

    /**
     * Deletes a specific MonitoringSet from JSON data
     *
     * @codeCoverageIgnore
     *
     * @param string $id
     *
     * @return void
     */
    public function deleteMonitoringSet($id)
    {
        $this->monitoringSetManager->deleteById($id);
    }
}