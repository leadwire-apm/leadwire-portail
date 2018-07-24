<?php

namespace Tests\ATS\TranslationBundle\Repository;

use ATS\TranslationBundle\Document\TranslationEntry;
use ATS\TranslationBundle\Manager\TranslationEntryManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TranslationEntryRepositoryTest extends KernelTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->documentRegistry = $kernel->getContainer()->get('doctrine_mongodb');
        $this->appCache = $kernel->getContainer()->get('cache.app');
        $this->documentManager = $kernel->getContainer()
            ->get('doctrine_mongodb')
            ->getManager();

        $this->object = new TranslationEntry('someKey', ['fr' => 'Une Valeur', 'en' => 'A Value']);
        $this->documentManager->persist($this->object);
        $this->documentManager->flush();

        $this->tem = new TranslationEntryManager($this->documentRegistry, $this->appCache);
    }

    public function testGetAllKeys()
    {
        $this->tem->deleteAll();

        $translationEntry = new TranslationEntry();
        $translationEntry->setKey('someKey')
            ->setValues([
                'fr' => 'Une Valeur',
                'en' => 'A Value',
            ]);

        $this->tem->update($translationEntry);

        $keys = $this->documentManager->getRepository(TranslationEntry::class)->getAllKeys();
        $this->assertCount(1, $keys);
        $this->assertCount(2, $keys[0]);
        $this->assertEquals('someKey', $keys[0]['key']);
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
