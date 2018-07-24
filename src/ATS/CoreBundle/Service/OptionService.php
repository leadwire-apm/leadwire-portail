<?php declare(strict_types=1);

namespace ATS\CoreBundle\Service;

use ATS\CoreBundle\Document\Option;
use ATS\CoreBundle\Manager\OptionManager;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;

/**
 * Service class for Option entities
 *
 */
class OptionService
{
    private $optionManager;

    private $serializer;

    private $logger;

    /**
     * Constructor
     *
     * @param OptionManager $optionManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(OptionManager $optionManager, SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->optionManager = $optionManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * List all Options
     *
     * @return array
     */
    public function listOptions()
    {
        return $this->optionManager->getAll();
    }

    /**
     * Paginates through Options
     *
     * @return array
     */
    public function paginate($pageNumber = 1, $itemsPerPage = 20, array $criteria = [])
    {
        return $this->optionManager->paginate($criteria, $pageNumber, $itemsPerPage);
    }

    /**
     * Get a specific Option
     *
     * @return Option
     */
    public function getOption($id)
    {
        return $this->optionManager->getOneBy(['id' => $id]);
    }

    /**
     * Creates a new Option from JSON data
     *
     */
    public function newOption($json)
    {
        return $this->updateOption($json);
    }

    /**
     * Updates a specific Option from JSON data
     *
     */
    public function updateOption($json)
    {
        $isSuccessful = true;
        $id = null;

        try {
            $option = $this->serializer->deserialize($json, Option::class, 'json');
            $id = $this->optionManager->update($option);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $isSuccessful = $e->getMessage();
        }

        return $isSuccessful;
    }

    /**
     * Deletes a specific Option from JSON data
     *
     */
    public function deleteOption($id)
    {
        $this->optionManager->deleteById($id);
    }

    public function textSearch($term, $lang = 'en')
    {
        return $this->optionManager->textSearch($term);
    }
}
