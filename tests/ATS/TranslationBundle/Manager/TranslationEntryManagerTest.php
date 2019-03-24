<?php

namespace Tests\ATS\TranslationBundle\Manager;

use ATS\TranslationBundle\Cache\TranslationCacheWarmer;
use ATS\TranslationBundle\Document\TranslationEntry;
use ATS\TranslationBundle\Manager\TranslationEntryManager;
use ATS\TranslationBundle\Service\TranslationEntryService;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\AbstractAdapter;

/**
 * TranslationEntryManagerTest
 *
 * @author Mohamed BEN ABDA <mbenabda@ats-digital.com>
 */
class TranslationEntryManagerTest extends KernelTestCase
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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TranslationEntryManager
     */
    private $tem;

    /**
     * @var TranslationEntryService
     */
    private $tes;

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
        $this->serializer = $kernel->getContainer()->get('jms_serializer');
        $this->logger = $kernel->getContainer()->get('logger');
        $this->documentManager = $kernel->getContainer()
            ->get('doctrine_mongodb')
            ->getManager();

        $this->documentManager->getSchemaManager()->dropDatabases();

        $this->tem = new TranslationEntryManager($this->documentRegistry, $this->appCache);
        $this->tes = new TranslationEntryService($this->tem, $this->serializer, $this->logger);
        $this->tcw = new TranslationCacheWarmer($this->tem, $this->appCache);
    }

    /**
     *  Test get TranslationEntry by key and Language
     *
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::__construct
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::warmUp
     *
     * @uses ATS\TranslationBundle\Document\TranslationEntry::__construct
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getKey
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getValues
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getValueForLanguage
     *
     * @uses ATS\TranslationBundle\Service\TranslationEntryService::__construct
     *
     */
    public function testGetByKeyAndLanguage()
    {
        $this->documentManager->getSchemaManager()->dropDatabases();

        $firstTranslation = new TranslationEntry('firstKey', ['fr' => 'Premier', 'en' => 'First']);
        $this->documentManager->persist($firstTranslation);
        $this->documentManager->flush();

        $this->appCache->clear();
        $this->tcw->warmUp($this->cacheDir);

        $this->assertEquals('Premier', $this->tem->getByKeyAndLanguage('firstKey', 'fr'));
        $this->assertEquals(null, $this->tem->getByKeyAndLanguage('firstKey', 'de'));
        $this->assertEquals(null, $this->tem->getByKeyAndLanguage('SecondKey', 'fr'));

        $this->documentManager->getSchemaManager()->dropDatabases();
        $this->appCache->clear();

        $this->assertEquals(null, $this->tem->getByKeyAndLanguage('firstKey', 'fr'));

    }

    /**
     *  Test get TranslationEntries by Language
     *
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::__construct
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::warmUp
     *
     * @uses ATS\TranslationBundle\Document\TranslationEntry::__construct
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getKey
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getValues
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getValueForLanguage
     *
     * @uses ATS\TranslationBundle\Service\TranslationEntryService::__construct
     *
     * @uses ATS\TranslationBundle\Repository\TranslationEntryRepository::getAllKeys
     */
    public function testGetByLanguage()
    {
        $this->documentManager->getSchemaManager()->dropDatabases();

        $firstTranslation = new TranslationEntry('firstKey', ['fr' => 'Premier', 'en' => 'First']);
        $this->documentManager->persist($firstTranslation);
        $this->documentManager->flush();

        $this->appCache->clear();
        $this->tcw->warmUp($this->cacheDir);

        $this->assertEquals(['firstKey' => 'Premier'], $this->tem->getByLanguage('fr'));
        $this->assertEquals(['firstKey' => 'First'], $this->tem->getByLanguage('en'));
        $this->assertEquals(['firstKey' => null], $this->tem->getByLanguage('de'));
        $this->assertArrayHasKey('firstKey', $this->tem->getByLanguage('de'));

        $this->documentManager->getSchemaManager()->dropDatabases();
        $this->appCache->clear();

        $this->assertEquals([], $this->tem->getByLanguage('fr'));
        $this->assertArrayNotHasKey('firstKey', $this->tem->getByLanguage('fr'));
    }

    /**
     * Test get Available Languages
     *
     * @uses ATS\TranslationBundle\Document\TranslationEntry::__construct
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getKey
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getValues
     *
     * @uses ATS\TranslationBundle\Service\TranslationEntryService::__construct
     *
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::__construct
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::warmUp
     */
    public function testGetAvailableLanguages()
    {
        $this->documentManager->getSchemaManager()->dropDatabases();

        $firstTranslation = new TranslationEntry('firstKey', ['fr' => 'Premier', 'en' => 'First', 'it' => 'Primo']);
        $this->documentManager->persist($firstTranslation);
        $this->documentManager->flush();

        $this->appCache->clear();
        $this->tcw->warmUp($this->cacheDir);

        $this->assertEquals(['fr', 'en', 'it'], $this->tem->getAvailableLanguages());
        $this->assertFalse(in_array('de', $this->tem->getAvailableLanguages()));
        $this->assertTrue(in_array('fr', $this->tem->getAvailableLanguages()));

        $this->documentManager->getSchemaManager()->dropDatabases();
    }

    /**
     * Test insert TranslationEntry
     *
     * @uses ATS\TranslationBundle\Document\TranslationEntry::__construct
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getId
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getKey
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getValues
     *
     * @uses ATS\TranslationBundle\Service\TranslationEntryService::__construct
     *
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::__construct
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::warmUp
     */
    public function testUpdate()
    {
        $this->documentManager->getSchemaManager()->dropDatabases();

        $firstTranslation = new TranslationEntry('firstKey', ['fr' => 'Premier', 'en' => 'First']);

        $this->appCache->clear();
        $this->tcw->warmUp($this->cacheDir);

        $this->tem->update($firstTranslation);

        $translationEntry = $this->tem->getOneBy(['key' => 'firstKey']);

        $this->assertEquals(['fr' => 'Premier','en' => 'First'], $translationEntry->getValues());
        $this->assertEquals('firstKey', $translationEntry->getKey());

        $this->documentManager->getSchemaManager()->dropDatabases();
    }

    /**
     * Test delete TranslationEntry by id
     *
     * @uses ATS\TranslationBundle\Document\TranslationEntry::__construct
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getId
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getKey
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getValues
     *
     * @uses ATS\TranslationBundle\Service\TranslationEntryService::__construct
     *
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::__construct
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::warmUp
     */
    public function testDeleteById()
    {
        $this->documentManager->getSchemaManager()->dropDatabases();

        $firstTranslation = new TranslationEntry('firstKey', ['fr' => 'Premier', 'en' => 'First']);
        $toBeDeletedTranslation = new TranslationEntry('toBeDeleted', ['fr' => 'Au revoir', 'en' => 'See ya']);

        $this->appCache->clear();

        $this->tem->update($firstTranslation);
        $this->tem->update($toBeDeletedTranslation);

        $this->assertEquals(2, count($this->tem->getAll()));

        $this->tem->deleteById($firstTranslation->getId());
        $firstTranslationfromDB = $this->tem->getOneBy(['key' => 'firstKey']);
        $this->assertEquals(null, $firstTranslationfromDB);

        $this->assertEquals(1, count($this->tem->getAll()));

        $this->tem->deleteById($toBeDeletedTranslation->getId());
        $toBeDeletedTranslationfromDB = $this->tem->getOneBy(['key' => 'toBeDeleted']);
        $this->assertEquals(null, $toBeDeletedTranslationfromDB);

        $this->assertEquals(0, count($this->tem->getAll()));

    }

    /**
     * Test init Keys
     *
     * @uses ATS\TranslationBundle\Document\TranslationEntry::__construct
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getId
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getKey
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getValues
     *
     * @uses ATS\TranslationBundle\Repository\TranslationEntryRepository::getAllKeys
     *
     * @uses ATS\TranslationBundle\Service\TranslationEntryService::__construct
     *
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::__construct
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::warmUp
     */
    public function testInitKeys()
    {
        $this->documentManager->getSchemaManager()->dropDatabases();

        $keys = ['firstKey', 'secondKey', 'thirdKey'];

        $this->tem->initKeys($keys, 'fr');

        $this->assertEquals(['fr'], $this->tem->getAvailableLanguages());
        $this->assertNotEquals(['en'], $this->tem->getAvailableLanguages());

        $firstTranslationEntry = $this->tem->getOneBy(['key' => 'firstKey']);
        $this->assertEquals(['fr' => null], $firstTranslationEntry->getValues());

        $this->assertEquals(3, count($this->tem->getAll()));

        $this->documentManager->getSchemaManager()->dropDatabases();
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->documentManager->close();
        $this->documentManager = null; // avoid memory leaks
    }
}
