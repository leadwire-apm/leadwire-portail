<?php

namespace Tests\ATS\TranslationBundle\Manager;

use ATS\TranslationBundle\Document\TranslationEntry;
use ATS\TranslationBundle\Manager\TranslationEntryManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TranslationEntryManagerTest extends KernelTestCase
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

    public function testGetByKeyAndLanguage()
    {
        $translation = $this->tem->getOneBy(['key' => 'someKey']);
        $translation->setValues(
            [
                'fr' => 'Obtenir par cle et langue',
                'en' => 'get by key and value',
            ]
        );

        $this->tem->update($translation);

        $translation = $this->tem->getByKeyAndLanguage('someKey', 'fr');
        $this->assertEquals('Obtenir par cle et langue', $translation);

        $translation = $this->tem->getByKeyAndLanguage('someKey', 'it');
        $this->assertEquals(null, $translation);
    }

    public function testGetByLanguage()
    {
        $translation = $this->tem->getOneBy(['key' => 'someKey']);
        $translation->setValues(
            [
                'fr' => 'Obtenir par langue',
                'en' => 'get by language',
            ]
        );

        $this->tem->update($translation);
        $this->assertEquals(['someKey' => 'Obtenir par langue'], $this->tem->getByLanguage('fr'));
    }

    public function testGetAvailableLanguages()
    {
        $this->assertEquals(['fr', 'en'], $this->tem->getAvailableLanguages());
    }

    public function testUpdate()
    {
        $translation = $this->tem->getOneBy(['key' => 'someKey']);
        $translation->setValues(
            [
                'fr' => 'Une autre valeur',
                'en' => 'Some other value',
            ]
        );

        $this->tem->update($translation);
        $translation = $this->tem->getOneBy(['key' => 'someKey']);

        $this->assertEquals(
            [
                'fr' => 'Une autre valeur',
                'en' => 'Some other value',
            ],
            $translation->getValues()
        );

        $this->assertEquals('someKey', $translation->getKey());

    }

    public function testDeleteById()
    {
        $translation = new TranslationEntry('toBeDeleted', ['fr' => 'Au revoir', 'en' => 'See ya']);
        $this->tem->update($translation);

        $this->tem->deleteById($translation->getId());

        $fromDB = $this->tem->getOneBy(['key' => 'toBeDeleted']);

        $this->assertEquals(null, $fromDB);
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
