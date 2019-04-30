<?php declare (strict_types = 1);

namespace AppBundle\Service;

use Psr\Log\LoggerInterface;
use AppBundle\Document\ApplicationType;
use JMS\Serializer\SerializerInterface;
use AppBundle\Manager\MonitoringSetManager;
use AppBundle\Manager\ApplicationTypeManager;
use AppBundle\Exception\DuplicateApplicationTypeException;

/**
 * Service class for ApplicationType entities
 *
 */
class ApplicationTypeService
{
    /**
     * @var ApplicationTypeManager
     */
    private $applicationTypeManager;

    /**
     * @var MonitoringSetManager
     */
    private $msManager;

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
     * @param ApplicationTypeManager $applicationTypeManager
     * @param MonitoringSetManager $msManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        ApplicationTypeManager $applicationTypeManager,
        MonitoringSetManager $msManager,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->applicationTypeManager = $applicationTypeManager;
        $this->msManager = $msManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * List all applicationTypes
     * @codeCoverageIgnore
     *
     * @return array
     */
    public function listApplicationTypes()
    {
        return $this->applicationTypeManager->getAll();
    }

    /**
     * Paginates through ApplicationTypes
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
        return $this->applicationTypeManager->paginate($criteria, $pageNumber, $itemsPerPage);
    }

    /**
     * Get a specific applicationType
     *
     * @codeCoverageIgnore
     * @param string $id
     *
     * @return ApplicationType
     */
    public function getApplicationType($id)
    {
        return $this->applicationTypeManager->getOneBy(['id' => $id]);
    }

    /**
     * Get specific applicationTypes
     *
     * @codeCoverageIgnore
     * @param array $criteria
     *
     * @return array
     */
    public function getApplicationTypes(array $criteria = [])
    {
        return $this->applicationTypeManager->getBy($criteria);
    }

    /**
     * Creates a new applicationType from JSON data
     *
     * @codeCoverageIgnore
     * @param string $json
     *
     * @return bool
     */
    public function newApplicationType($json)
    {
        $dm = $this->msManager->getDocumentManager();

        /** @var ApplicationType $applicationType */
        $applicationType = $this->serializer->deserialize($json, ApplicationType::class, 'json');
        $dbApplicationType = $this->applicationTypeManager->getOneBy(
            [
                'name' => $applicationType->getName(),
            ]
        );

        if ($dbApplicationType instanceof ApplicationType) {
            throw new DuplicateApplicationTypeException("An application type with the same name already exists");
        } else {
            $applicationType->setVersion(1);
            $monitoringSets = $applicationType->getMonitoringSets();
            $applicationType->resetMonitoringSets();
            foreach ($monitoringSets as $ms) {
                $loaded = $this->msManager->getOneBy(['id' => $ms->getId()]);
                $applicationType->addMonitoringSet($loaded);
            }
            $dm->persist($applicationType);
            $dm->flush();
            return true;
        }
    }

    /**
     * Updates a specific applicationType from JSON data
     *
     * @codeCoverageIgnore
     *
     * @param string $json
     *
     * @return bool
     */
    public function updateApplicationType($json)
    {
        $isSuccessful = false;
        $dm = $this->msManager->getDocumentManager();

        try {
            /** @var ApplicationType $applicationType */
            $applicationType = $this->serializer->deserialize($json, ApplicationType::class, 'json');

            $applicationType->incrementVersion();
            $monitoringSets = $applicationType->getMonitoringSets();
            $applicationType->resetMonitoringSets();
            foreach ($monitoringSets as $ms) {
                $loaded = $this->msManager->getOneBy(['id' => $ms->getId()]);
                $applicationType->addMonitoringSet($loaded);
            }
            $dm->persist($applicationType);
            $dm->flush();
            $isSuccessful = true;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $isSuccessful;
    }

    /**
     * Deletes a specific applicationType from JSON data
     *
     * @codeCoverageIgnore
     *
     * @param string $id
     *
     * @return void
     */
    public function deleteApplicationType($id)
    {
        $this->applicationTypeManager->deleteById($id);
    }
}
