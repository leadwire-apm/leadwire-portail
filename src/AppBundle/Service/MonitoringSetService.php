<?php declare (strict_types = 1);

namespace AppBundle\Service;

use Psr\Log\LoggerInterface;
use AppBundle\Document\Template;
use AppBundle\Document\MonitoringSet;
use AppBundle\Manager\TemplateManager;
use JMS\Serializer\SerializerInterface;
use AppBundle\Manager\MonitoringSetManager;
use AppBundle\Manager\ApplicationTypeManager;

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
     * @var ApplicationTypeManager
     */
    private $applicationTypeManager;

    /**
     * @var TemplateManager
     */
    private $templateManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     *
     * @param MonitoringSetManager $monitoringSetManager
     * @param ApplicationTypeManager $applicationTypeManager
     * @param TemplateManager $templateManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        MonitoringSetManager $monitoringSetManager,
        ApplicationTypeManager $applicationTypeManager,
        TemplateManager $templateManager,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->monitoringSetManager = $monitoringSetManager;
        $this->applicationTypeManager = $applicationTypeManager;
        $this->templateManager = $templateManager;
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

    public function listValidMonitoringSets()
    {
        return $this->monitoringSetManager->getValid();
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
        /** @var MonitoringSet $monitoringSet */
        $monitoringSet = $this->serializer->deserialize($json, MonitoringSet::class, 'json');
        $templates = $monitoringSet->getTemplates();
        $monitoringSet->resetTemplates();
        foreach ($templates as $template) {
            $loaded = $this->templateManager->getOneBy(['id' => $template->getId()]);
            if (($loaded instanceof Template) === false) {
                continue;
            }
            $monitoringSet->addTemplate($loaded);
        }
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
            /** @var MonitoringSet $monitoringSet */
            $monitoringSet = $this->serializer->deserialize($json, MonitoringSet::class, 'json');

            $dbDocument = $this->monitoringSetManager->getOneBy(['id' => $monitoringSet->getId()]);

            if ($dbDocument instanceof MonitoringSet) {
                $dbDocument->setVersion($monitoringSet->getVersion());
                $dbDocument->setQualifier($monitoringSet->getQualifier());
                $dbDocument->setName($monitoringSet->getName());
                $dbDocument->setVersion($monitoringSet->getVersion());
                $dbDocument->resetTemplates();
                foreach ($monitoringSet->getTemplates() as $template) {
                    $loaded = $this->templateManager->getOneBy(['id' => $template->getId()]);
                    if (($loaded instanceof Template) === false) {
                        continue;
                    }
                    $dbDocument->addTemplate($loaded);
                }
                $this->monitoringSetManager->update($dbDocument);
            }
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
        $ms = $this->monitoringSetManager->getOneBy(['id' => $id]);

        $linkedTypes = $this->applicationTypeManager->getLinkedTypes($ms);

        if (count($linkedTypes) > 0) {
            throw new \Exception("Cannot delete a monitoring set that is used in an application type");
        }

        if ($ms instanceof MonitoringSet) {
            $this->monitoringSetManager->deleteById($id);
        }
    }
}
