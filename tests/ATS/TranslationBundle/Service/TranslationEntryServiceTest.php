<?php

namespace Tests\ATS\TranslationBundle\Service;

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
 * TranslationEntryServiceTest
 *
 * @author Mohamed BEN ABDA <mbenabda@ats-digital.com>
 */
class TranslationEntryServiceTest extends KernelTestCase
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
     * Test List all TranslationEntries
     *
     * @uses ATS\TranslationBundle\Document\TranslationEntry::__construct
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::__construct
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::__construct
     */
    public function testListTranslationEntries()
    {
        $firstTranslation = new TranslationEntry('firstKey', ['fr' => 'Premier', 'en' => 'First']);
        $this->documentManager->persist($firstTranslation);

        $secondTranslation = new TranslationEntry('secondKey', ['fr' => 'Deuxième', 'en' => 'Second']);
        $this->documentManager->persist($secondTranslation);

        $thirdTranslation = new TranslationEntry('thirdKey', ['fr' => 'Troisième', 'en' => 'Third']);
        $this->documentManager->persist($thirdTranslation);

        $this->documentManager->flush();

        $this->assertEquals(3, count($this->tes->listTranslationEntries()));

        $this->documentManager->getSchemaManager()->dropDatabases();
    }

    /**
     * Test List all TranslationEntries in language ->  { key : value } format
     *
     * @uses ATS\TranslationBundle\Document\TranslationEntry::__construct
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getKey
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getValues
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::__construct
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::__construct
     */
    public function testListCompactTranslationEntries()
    {
        $firstTranslation = new TranslationEntry('firstKey', ['fr' => 'Premier', 'en' => 'First']);
        $this->documentManager->persist($firstTranslation);

        $secondTranslation = new TranslationEntry('secondKey', ['fr' => 'Deuxième', 'en' => 'Second']);
        $this->documentManager->persist($secondTranslation);

        $this->documentManager->flush();

        $compactTranslationEntries = $this->tes->listCompactTranslationEntries();
        $this->assertEquals(2, count($compactTranslationEntries));

        $this->assertArrayHasKey('fr', $compactTranslationEntries);
        $this->assertArrayHasKey('firstKey', $compactTranslationEntries['fr']);

        $this->documentManager->getSchemaManager()->dropDatabases();
    }

    /**
     * Test Get a specific TranslationEntry by Id
     *
     * @uses ATS\TranslationBundle\Document\TranslationEntry::__construct
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getId
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getKey
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getValues
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getValueForLanguage
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::__construct
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::__construct
     */
    public function testGetTranslationEntry()
    {
        $translation = new TranslationEntry('firstKey', ['fr' => 'Premier', 'en' => 'First']);
        $this->documentManager->persist($translation);
        $this->documentManager->flush();

        $fromDbTranslation = $this->tes->getTranslationEntry($translation->getId());

        $this->assertNotEquals(null, $fromDbTranslation);
        $this->assertEquals('firstKey', $fromDbTranslation->getKey());
        $this->assertEquals(['fr' => 'Premier', 'en' => 'First'], $fromDbTranslation->getValues());
        $this->assertEquals('Premier', $fromDbTranslation->getValueForLanguage('fr'));
        $this->assertEquals('First', $fromDbTranslation->getValueForLanguage('en'));
        $this->assertEquals(null, $fromDbTranslation->getValueForLanguage('de'));

        $this->documentManager->getSchemaManager()->dropDatabases();
    }

    /**
     * Test Create a new TranslationEntry from JSON data
     *
     * @uses ATS\TranslationBundle\Document\TranslationEntry::__construct
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getId
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getKey
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getValues
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getValueForLanguage
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::__construct
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::update
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::__construct
     */
    public function testNewTranslationEntry()
    {
        $jsonFirstTranslationEntry = json_encode( ['key' =>'firstKey', 'values' => ['fr' => 'Premier', 'en' => 'First']]);
        $this->tes->newTranslationEntry($jsonFirstTranslationEntry);

        $fromDbTranslation = $this->tem->getOneBy(['key' => 'firstKey']);

        $this->assertNotEquals(null, $fromDbTranslation);
        $this->assertEquals('firstKey', $fromDbTranslation->getKey());
        $this->assertEquals(['fr' => 'Premier', 'en' => 'First'], $fromDbTranslation->getValues());
        $this->assertEquals('Premier', $fromDbTranslation->getValueForLanguage('fr'));
        $this->assertEquals('First', $fromDbTranslation->getValueForLanguage('en'));
        $this->assertEquals(null, $fromDbTranslation->getValueForLanguage('de'));

        $jsonSecondTranslationEntry = json_encode( ['firstKey', ['fr' => 'Premier', 'en' => 'First']]);
        $exception = $this->tes->newTranslationEntry($jsonSecondTranslationEntry);

        $this->assertEquals(false, $exception);

        $this->documentManager->getSchemaManager()->dropDatabases();
    }

    /**
     * Test Updates a specific TranslationEntry from JSON data
     *
     * @uses ATS\TranslationBundle\Document\TranslationEntry::__construct
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getId
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getKey
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getValues
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getValueForLanguage
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::__construct
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::update
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::__construct
     */
    public function testUpdateTranslationEntry()
    {
        $translation = new TranslationEntry('firstKey', ['fr' => 'Premier', 'en' => 'First']);
        $this->documentManager->persist($translation);
        $this->documentManager->flush();

        $fromDbTranslation = $this->tes->getTranslationEntry($translation->getId());

        $this->assertNotEquals(null, $fromDbTranslation);
        $this->assertEquals('firstKey', $fromDbTranslation->getKey());
        $this->assertEquals(['fr' => 'Premier', 'en' => 'First'], $fromDbTranslation->getValues());

        $jsonToUpdateTranslationEntry = json_encode( ['key' =>'UpdatedKey', 'values' => ['fr' => 'Mis à jour', 'en' => 'Updated']]);
        $this->tes->updateTranslationEntry($jsonToUpdateTranslationEntry, $fromDbTranslation->getId());

        $fromDbUpdatedTranslation = $this->tes->getTranslationEntry($translation->getId());

        $this->assertNotEquals(null, $fromDbUpdatedTranslation);
        $this->assertEquals('firstKey', $fromDbUpdatedTranslation->getKey());
        $this->assertEquals(['fr' => 'Premier', 'en' => 'First'], $fromDbUpdatedTranslation->getValues());
        $this->assertEquals('firstKey', $fromDbUpdatedTranslation->getKey());
        // $this->assertEquals(['fr' => 'Mis à jour', 'en' => 'Updated'], $fromDbUpdatedTranslation->getValues());

        $this->documentManager->getSchemaManager()->dropDatabases();
    }

    /**
     * Test Deletes a specific TranslationEntry
     *
     * @uses ATS\TranslationBundle\Document\TranslationEntry::__construct
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getId
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getKey
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getValues
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getValueForLanguage
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::__construct
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::update
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::deleteById
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::__construct
     */
    public function testDeleteById()
    {
        $translation = new TranslationEntry('toBeDeleted', ['fr' => 'Au revoir', 'en' => 'See ya']);
        $this->documentManager->persist($translation);
        $this->documentManager->flush();

        $this->assertEquals(1, count($this->tem->getAll()));

        $fromDbToDeleteTranslation = $this->tes->getTranslationEntry($translation->getId());

        $this->assertNotEquals(null, $fromDbToDeleteTranslation);
        $this->assertEquals('toBeDeleted', $fromDbToDeleteTranslation->getKey());
        $this->assertEquals(['fr' => 'Au revoir', 'en' => 'See ya'], $fromDbToDeleteTranslation->getValues());

        $this->tes->deleteById($fromDbToDeleteTranslation->getId());

        $this->assertEquals(0, count($this->tem->getAll()));
        $this->assertEquals(null, $this->tes->getTranslationEntry($fromDbToDeleteTranslation->getId()));
    }

    /**
     * Test Get TranslationEntries by Language
     *
     * @uses ATS\TranslationBundle\Document\TranslationEntry::__construct
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getId
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getKey
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getValues
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getValueForLanguage
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::__construct
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::update
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::deleteById
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::getByKeyAndLanguage
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::getByLanguage
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::__construct
     */
    public function testGetByLanguage()
    {
        $this->appCache->clear();
        $translation = new TranslationEntry('firstKey', ['fr' => 'Premier', 'en' => 'First']);
        $this->tem->update($translation);

        $this->assertEquals(1, count($this->tem->getAll()));

        $fromDbTranslation = $this->tes->getByLanguage('fr');

        $this->assertNotEquals(null, $fromDbTranslation);
        $this->assertEquals(['firstKey' => 'Premier'], $fromDbTranslation);

        $this->documentManager->getSchemaManager()->dropDatabases();
    }

    /**
     * Test Get Available Languages
     *
     * @uses ATS\TranslationBundle\Document\TranslationEntry::__construct
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getId
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getKey
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getValues
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::__construct
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::update
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::getAvailableLanguages
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::__construct
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::warmUp
     */
    public function testGetAvailableLanguages()
    {
        $firstTranslation = new TranslationEntry('firstKey', ['fr' => 'Premier', 'en' => 'First', 'it' => 'Primo']);
        $this->tem->update($firstTranslation);

        $this->appCache->clear();
        $this->tcw->warmUp($this->cacheDir);

        $this->assertEquals(['fr', 'en', 'it'], $this->tes->getAvailableLanguages());
        $this->assertFalse(in_array('de', $this->tes->getAvailableLanguages()));
        $this->assertTrue(in_array('fr', $this->tes->getAvailableLanguages()));

        $this->documentManager->getSchemaManager()->dropDatabases();
    }

    /**
     * Test Add New Language
     *
     * @uses ATS\TranslationBundle\Document\TranslationEntry::__construct
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getId
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getKey
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getValues
     * @uses ATS\TranslationBundle\Document\TranslationEntry::addValue
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::__construct
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::update
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::getAvailableLanguages
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::__construct
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::warmUp
     */
    public function testAddNewLanguage()
    {
        $firstTranslation = new TranslationEntry('firstKey', ['fr' => 'Premier', 'en' => 'First']);
        $this->tem->update($firstTranslation);

        $this->appCache->clear();
        $this->tcw->warmUp($this->cacheDir);

        $this->assertEquals(['fr', 'en'], $this->tes->getAvailableLanguages());
        $this->assertEquals(2, count($this->tes->getAvailableLanguages()));
        $this->assertFalse(in_array('it', $this->tes->getAvailableLanguages()));

        $this->tes->addNewLanguage('it');

        $this->assertEquals(['fr', 'en', 'it'], $this->tes->getAvailableLanguages());
        $this->assertEquals(3, count($this->tes->getAvailableLanguages()));
        $this->assertTrue(in_array('it', $this->tes->getAvailableLanguages()));

        $this->documentManager->getSchemaManager()->dropDatabases();
    }

    /**
     * Test Init Keys
     *
     * @uses ATS\TranslationBundle\Document\TranslationEntry::__construct
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getId
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getKey
     * @uses ATS\TranslationBundle\Document\TranslationEntry::getValues
     * @uses ATS\TranslationBundle\Repository\TranslationEntryRepository::getAllKeys
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::__construct
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::getAvailableLanguages
     * @uses ATS\TranslationBundle\Manager\TranslationEntryManager::initKeys
     * @uses ATS\TranslationBundle\Service\TranslationEntryService::__construct
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::__construct
     * @uses ATS\TranslationBundle\Cache\TranslationCacheWarmer::warmUp
     */
    public function testInitKeys()
    {
        $keys = ['firstKey', 'secondKey', 'thirdKey'];

        $this->tes->initKeys($keys);

        $this->appCache->clear();
        $this->tcw->warmUp($this->cacheDir);

        $this->assertEquals(['en'], $this->tes->getAvailableLanguages());
        $this->assertNotEquals(['fr'], $this->tes->getAvailableLanguages());
        $this->assertEquals(3, count($this->tem->getAll()));

        $this->documentManager->getSchemaManager()->dropDatabases();
    }
}
