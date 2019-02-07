<?php

namespace Tests\ATS\TranslationBundle\Cache;

use ATS\TranslationBundle\Cache\TranslationCacheWarmer;
use ATS\TranslationBundle\Document\TranslationEntry;
use ATS\TranslationBundle\Manager\TranslationEntryManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\AbstractAdapter;

/**
 * TranslationCacheWarmerTest
 *
 * @author Mohamed BEN ABDA <mbenabda@ats-digital.com>
 */
class TranslationCacheWarmerTest extends KernelTestCase
{
    /**
     * @var DocumentManager
     */

    private $documentManager;

    /**
     * @var ManagerRegistry
     */

    private $documentRegistry;

    /**
     * @var AbstractAdapter
     */

    private $appCache;

    /**
     * @var TranslationEntryManager
     */
    private $tem;

    /**
     * @var TranslationCacheWarmer
     */
    private $tcw;

    /**
     * @var string
     */
    private $cacheDir;
    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->documentRegistry = $kernel->getContainer()->get('doctrine_mongodb');
        $this->appCache = $kernel->getContainer()->get('cache.app');
        $this->cacheDir = $kernel->getCacheDir();
        $this->documentManager = $kernel->getContainer()
            ->get('doctrine_mongodb')
            ->getManager();

        $this->documentManager->getSchemaManager()->dropDatabases();

        $this->tem = new TranslationEntryManager($this->documentRegistry, $this->appCache);
        $this->tcw = new TranslationCacheWarmer($this->tem, $this->appCache);
    }

    /**
     * Test IsOptional
     *
     * @uses ATS\TranslationBundle\Document\TranslationEntry::__construct
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::__construct
     * @uses ATS\TranslationBundle\Service\TranslationEntryService::__construct
     */
    public function testIsOptional()
    {
        $this->assertTrue($this->tcw->isOptional());
    }

    /**
     * Test WarmUp
     *
     * @uses ATS\TranslationBundle\Document\TranslationEntry::__construct
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getKey
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getValueForLanguage
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getValues
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::__construct
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::getAvailableLanguages
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::getByKeyAndLanguage
     * @uses ATS\TranslationBundle\Service\TranslationEntryService::__construct
     */
    public function testWarmUp()
    {
        $firstTranslation = new TranslationEntry('firstKey', ['fr' => 'Premier', 'en' => 'First', 'it' => 'Primo']);
        $this->documentManager->persist($firstTranslation);
        $this->documentManager->flush();

        $this->appCache->clear();
        $this->tcw->warmUp($this->cacheDir);

        $this->assertEquals(['fr', 'en', 'it'], $this->tem->getAvailableLanguages());
        $this->assertFalse(in_array('de', $this->tem->getAvailableLanguages()));
        $this->assertTrue(in_array('fr', $this->tem->getAvailableLanguages()));


        $this->assertEquals('Premier', $this->tem->getByKeyAndLanguage('firstKey', 'fr'));
        $this->assertEquals(null, $this->tem->getByKeyAndLanguage('firstKey', 'de'));
        $this->assertEquals(null, $this->tem->getByKeyAndLanguage('SecondKey', 'fr'));

        $this->documentManager->getSchemaManager()->dropDatabases();
    }
}
