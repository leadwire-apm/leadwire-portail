<?php declare (strict_types = 1);

namespace ATS\TranslationBundle\Manager;

use ATS\CoreBundle\Manager\AbstractManager;
use ATS\TranslationBundle\Document\TranslationEntry;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;

class TranslationEntryManager extends AbstractManager
{
    /**
     *
     * @var CacheInterface
     */
    private $cache;

    public function __construct(ManagerRegistry $managerRegistry, CacheItemPoolInterface $cache, $managerName = null)
    {
        $this->cache = $cache;

        parent::__construct($managerRegistry, TranslationEntry::class, $managerName);
    }

    public function getByKeyAndLanguage($key, $language)
    {
        $cacheItem = $this->cache->getItem("$language.$key");

        if ($cacheItem->isHit()) {
            $translation = $cacheItem->get();
        } else {
            $translationEntry = $this->getOneBy(['key' => $key]);

            if ($translationEntry) {
                $translation = $translationEntry->getValueForLanguage($language);
                $cacheItem->set($translation);
                $this->cache->save($cacheItem);
            } else {
                $translation = null;
            }
        }

        return $translation;
    }

    public function getByLanguage($language)
    {
        $translations = [];

        $cacheItem = $this->cache->getItem('translation.keys');
        if ($cacheItem->isHit()) {
            $keys = $cacheItem->get();
        } else {
            $keys = $this->getDocumentRepository()->getAllKeys();
        }

        foreach ($keys as $key) {
            $translations[$key] = $this->getByKeyAndLanguage($key, $language);
        }

        return $translations;
    }

    public function getAvailableLanguages()
    {
        $allEntries = $this->getAll();
        $languages = [];

        foreach ($allEntries as $translationEntry) {
            foreach ($translationEntry->getValues() as $lang => $text) {
                $languages[] = $lang;
            }
        }

        return array_unique($languages);
    }

    /**
     * @param $translation
     */
    public function update($translation)
    {
        $this->getDocumentRepository()
            ->save($translation);

        $key = $translation->getKey();

        foreach ($translation->getValues() as $language => $text) {
            $cacheItem = $this->cache->getItem("$language.$key");
            $cacheItem->set($text);
            $this->cache->save($cacheItem);
        }

        $translationKeysCacheItem = $this->cache->getItem('translation.keys');
        $keys = $translationKeysCacheItem->get();
        $keys[] = $key;
        $translationKeysCacheItem->set($keys);
        $this->cache->save($translationKeysCacheItem);
    }

    public function deleteById($id)
    {
        $translation = $this->getOneBy(['id' => $id]);
        if ($translation) {
            $translationKey = $translation->getKey();

            $cacheKeys = [];

            foreach ($translation->getValues() as $lang => $text) {
                $cacheKeys[] = "$lang.$translationKey";
            }

            foreach ($cacheKeys as $cacheKey) {
                $this->cache->deleteItem($cacheKey);
            }

            $translationKeysCacheItem = $this->cache->getItem('translation.keys');
            $keys = $translationKeysCacheItem->get();
            $keys = array_diff($keys, [$translationKey]);

            if (count($keys)) {
                $translationKeysCacheItem->set($keys);
                $this->cache->save($translationKeysCacheItem);
            } else {
                $this->cache->deleteItem('translation.keys');
            }

            $this->delete($translation);
        }
    }

    /**
     * @param $keys string
     * @param $locale string
     */
    public function initKeys($keys, $locale)
    {
        $tEntries = [];

        $this->deleteAll();

        foreach ($keys as $key) {
            $tEntries[] = new TranslationEntry($key, [$locale => null]);
        }

        $this->batchUpdate($tEntries);
    }
}
