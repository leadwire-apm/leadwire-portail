<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\ApplicationType;
use AppBundle\Manager\ApplicationTypeManager;
use GuzzleHttp\Client;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;

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
     * @var string
     */
    private $dataRootPath;

    /**
     * Constructor
     *
     * @param ApplicationTypeManager $applicationTypeManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @param string $dataRootPath
     */
    public function __construct(
        ApplicationTypeManager $applicationTypeManager,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        string $dataRootPath
    ) {
        $this->applicationTypeManager = $applicationTypeManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->dataRootPath = $dataRootPath;
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
     * @param string $json
     *
     * @return bool
     */
    public function newApplicationType($json)
    {
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
     * @return ApplicationType
     */
    public function createDefaultType()
    {
        $defaultType = $this->applicationTypeManager->getOneBy(['name' => "Java"]);
        if ($defaultType === null) {
            $client = new Client();
            $url = "https://github.com/leadwire-apm/leadwire-javaagent";
            $response = $client->get($url . "/raw/stable/README.md", ['stream' => true]);
            $defaultType = new ApplicationType();
            $defaultType->setName("Java");
            $defaultType->setInstallation($response->getBody()->read(10000));
            $content = file_get_contents(
                $this->dataRootPath . "/apm-dashboards.json"
            );

            if ($content !== false) {
                $defaultType->setTemplate(json_decode($content));
            } else {
                throw new \Exception(
                    sprintf(
                        "Bad content from file %s",
                        $this->dataRootPath . "/apm-dashboards.json"
                    )
                );
            }
            $defaultType->setAgent($url);
            $this->applicationTypeManager->update($defaultType);
        }

        return $defaultType;
    }
}
