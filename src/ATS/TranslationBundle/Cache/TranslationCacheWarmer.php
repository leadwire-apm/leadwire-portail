<?php declare(strict_types=1);

namespace ATS\TranslationBundle\Cache;

use ATS\TranslationBundle\Manager\TranslationEntryManager;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class TranslationCacheWarmer implements CacheWarmerInterface
{

    private $translationManager;
    private $cache;

    public function __construct(TranslationEntryManager $translationManager, CacheItemPoolInterface $cache)
    {
        $this->translationManager = $translationManager;
        $this->cache = $cache;
    }

    public function isOptional()
    {
        return true;
    }

    public function warmUp($cacheDir)
    {
        $keys = [];
        foreach ($this->translationManager->getAll() as $translationEntry) {
            $key = $translationEntry->getKey();
            $keys[] = $key;
            foreach ($translationEntry->getValues() as $language => $translation) {
                $cacheItem = $this->cache->getItem("$language.$key");
                $cacheItem->set($translation);
                $this->cache->save($cacheItem);
            }
        }

        $cacheItem = $this->cache->getItem("translation.keys");
        $cacheItem->set($keys);
        $this->cache->save($cacheItem);
    }
}
