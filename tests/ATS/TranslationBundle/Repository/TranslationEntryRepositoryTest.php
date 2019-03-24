<?php

namespace Tests\ATS\TranslationBundle\Repository;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use ATS\TranslationBundle\Document\TranslationEntry;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

/**
 * TranslationEntryManagerTest
 *
 * @author Mohamed BEN ABDA <mbenabda@ats-digital.com>
 */
class TranslationEntryRepositoryTest extends KernelTestCase
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
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->documentRegistry = $kernel->getContainer()->get('doctrine_mongodb');
        $this->documentManager = $kernel->getContainer()
            ->get('doctrine_mongodb')
            ->getManager();

        $this->documentManager->getSchemaManager()->dropDatabases();
    }

    /**
     * Test Get All Keys
     *
     * @uses ATS\TranslationBundle\Document\TranslationEntry::__construct
     */
    public function testGetAllKeys()
    {
        $firstTranslation = new TranslationEntry('firstKey', ['fr' => 'Premier', 'en' => 'First']);
        $this->documentManager->persist($firstTranslation);

        $secondTranslation = new TranslationEntry('secondKey', ['fr' => 'Deuxième', 'en' => 'Second']);
        $this->documentManager->persist($secondTranslation);

        $this->documentManager->flush();

        $translationEntries = $this->documentManager
            ->getRepository(TranslationEntry::class)
            ->getAllKeys()
        ;

        $this->assertCount(2, $translationEntries);

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
