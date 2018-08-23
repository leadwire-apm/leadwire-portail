<?php declare(strict_types=1);

namespace AppBundle\Service;

use ATS\CoreBundle\Service\Http\GuzzleClient;
use Psr\Log\LoggerInterface;
use JMS\Serializer\SerializerInterface;
use AppBundle\Manager\ApplicationTypeManager;
use AppBundle\Document\ApplicationType;

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
    public function __construct(ApplicationTypeManager $applicationTypeManager, SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->applicationTypeManager = $applicationTypeManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * List all applicationTypes
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
     * @param string $criteria
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
     * @param string $json
     *
     * @return bool
     */
    public function newApplicationType($json)
    {
        $applicationType = $this
                ->serializer
                ->deserialize($json, ApplicationType::class, 'json');

        return $this->updateApplicationType($json);
    }

    /**
     * Updates a specific applicationType from JSON data
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
            $isSuccessful = false;
        }

        return $isSuccessful;
    }

    /**
     * Deletes a specific applicationType from JSON data
     *
     * @param string $id
     *
     * @return void
     */
    public function deleteApplicationType($id)
    {
         $this->applicationTypeManager->deleteById($id);
    }

     /**
      * Performs a full text search on  ApplicationType
      *
      * @param string $term
      * @param string $lang
      *
      * @return array
      */
    public function textSearch($term, $lang)
    {
        return $this->applicationTypeManager->textSearch($term, $lang);
    }

    /**
     * Performs multi-field grouped query on ApplicationType
     * @param array $searchCriteria
     * @param string $groupField
     * @param \Closure $groupValueProcessor
     * @return array
     */
    public function getAndGroupBy(array $searchCriteria, $groupFields = [], $valueProcessors = [])
    {
        return $this->applicationTypeManager->getAndGroupBy($searchCriteria, $groupFields, $valueProcessors);
    }

    /**
     * @return ApplicationType
     */
    public function createDefaultType()
    {
        $client = new GuzzleClient();
        $url = "https://github.com/leadwire-apm/leadwire-javaagent";
        $response = $client->get($url . "/raw/stable/README.md", ['stream' => true]);
        $defaultType = new ApplicationType();
        $defaultType->setName("Java");
        $defaultType->setInstallation($response->getBody()->read(10000));
        $defaultType->setTemplate(json_decode(file_get_contents(__DIR__ . "/../../../app/Resources/Kibana/apm-dashboards.json")));
        $defaultType->setAgent($url);
        $this->applicationTypeManager->update($defaultType);
        return $defaultType;
    }
}
