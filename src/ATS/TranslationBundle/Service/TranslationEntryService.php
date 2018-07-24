<?php declare (strict_types = 1);

namespace ATS\TranslationBundle\Service;

use ATS\TranslationBundle\Document\TranslationEntry;
use ATS\TranslationBundle\Manager\TranslationEntryManager;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;

/**
 * Service class for TranslationEntry entities
 *
 */
class TranslationEntryService
{
    private $translationEntryManager;

    private $serializer;

    private $logger;

    private $locale;

    /**
     * Constructor
     *
     * @param TranslationEntryManager $translationEntryManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        TranslationEntryManager $translationEntryManager,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        $locale = 'en'
    ) {
        $this->translationEntryManager = $translationEntryManager;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->locale = $locale;
    }

    /**
     * List all TranslationEntries
     *
     * @return array
     */
    public function listTranslationEntries()
    {
        return $this->translationEntryManager->getAll();
    }

    /**
     * List all TranslationEntries in language ->  { key : value } format
     *
     * @return array
     */

    public function listCompactTranslationEntries()
    {
        $allEntries = $this->translationEntryManager->getAll();

        $result = [];

        foreach ($allEntries as $entry) {
            foreach ($entry->getValues() as $lang => $value) {
                $result[$lang][$entry->getKey()] = $value;
            }
        }

        return $result;
    }

    /**
     * Get a specific TranslationEntry
     *
     * @return TranslationEntry
     */
    public function getTranslationEntry($id)
    {
        return $this->translationEntryManager->getOneBy(['id' => $id]);
    }

    /**
     * Creates a new TranslationEntry from JSON data
     *
     */
    public function newTranslationEntry($json)
    {
        return $this->updateTranslationEntry($json);
    }

    /**
     * Updates a specific TranslationEntry from JSON data
     *
     */
    public function updateTranslationEntry($json)
    {
        $isSuccessful = false;

        try {
            $TranslationEntry = $this
                ->serializer
                ->deserialize($json, TranslationEntry::class, 'json');
            $this->translationEntryManager->update($TranslationEntry);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $isSuccessful = false;
        }

        return $isSuccessful;
    }

    /**
     * Deletes a specific TranslationEntry
     *
     */
    public function deleteById($id)
    {
        $this->translationEntryManager->deleteById($id);
    }

    public function getByLanguage($language)
    {
        return $this->translationEntryManager->getByLanguage($language);
    }

    public function getAvailableLanguages()
    {
        return $this->translationEntryManager->getAvailableLanguages();
    }

    public function addNewLanguage($newLanguage)
    {
        $entries = [];
        foreach ($this->translationEntryManager->getAll() as $entry) {
            $entry->addValue($newLanguage);
            $entries[] = $entry;
        }

        $this->translationEntryManager->batchUpdate($entries);
    }

    /**
     * Seeds keys from frontend and initializes empty values for fallback locale
     *
     * @param string $keys
     */

    public function initKeys($keys)
    {
        $this->translationEntryManager->initKeys($keys, $this->locale);
    }
}
