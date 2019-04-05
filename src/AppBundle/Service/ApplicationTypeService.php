<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\ApplicationType;
use AppBundle\Manager\ApplicationTypeManager;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
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
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        ApplicationTypeManager $applicationTypeManager,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->applicationTypeManager = $applicationTypeManager;
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
            $this->applicationTypeManager->update($applicationType);
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

        try {
            $applicationType = $this->serializer->deserialize($json, ApplicationType::class, 'json');
            $this->applicationTypeManager->update($applicationType);
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
