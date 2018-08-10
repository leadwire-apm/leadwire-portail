<?php

namespace Tests\ATS\CoreBundle\Manager;

use ATS\CoreBundle\Manager\AbstractManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AbstractManagerTest extends KernelTestCase
{
    protected $object;

    protected $documentManager;
    protected $documentRegistry;
    protected $appCache;

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
    }

    public function testPaginate()
    {
        $manager = $this->createMock(AbstractManager::class);
        $manager->expects($this->any())
            ->method('paginate')
            ->willReturn($this->object);

        $this->assertEquals($this->object, $manager->paginate());
    }

    public function testUpdate()
    {
        $manager = $this->createMock(AbstractManager::class);
        $manager->expects($this->any())
            ->method('update')
            ->willReturn(null);

        $this->assertEquals(null, $manager->update($this->object));
    }

    public function testDelete()
    {
        $manager = $this->createMock(AbstractManager::class);
        $manager->expects($this->any())
            ->method('delete')
            ->willReturn(null);

        $this->assertEquals(null, $manager->delete($this->object));
    }

    public function testDeleteById()
    {
        $manager = $this->createMock(AbstractManager::class);
        $manager->expects($this->any())
            ->method('deleteById')
            ->willReturn(null);

        $this->assertEquals(null, $manager->deleteById(1));
    }

    public function testDeleteAll()
    {
        $manager = $this->createMock(AbstractManager::class);
        $manager->expects($this->any())
            ->method('deleteAll')
            ->willReturn(null);

        $this->assertEquals(null, $manager->deleteAll());
    }

    public function testGetAll()
    {
        $manager = $this->createMock(AbstractManager::class);
        $manager->expects($this->any())
            ->method('getAll')
            ->willReturn($this->object);

        $this->assertEquals($this->object, $manager->getAll());
    }

    public function testGetBy()
    {
        $manager = $this->createMock(AbstractManager::class);
        $manager->expects($this->any())
            ->method('getBy')
            ->willReturn($this->object);

        $this->assertEquals($this->object, $manager->getBy(['firstAttr' => 1]));
    }

    public function testGetOneBy()
    {
        $manager = $this->createMock(AbstractManager::class);
        $manager->expects($this->any())
            ->method('getOneBy')
            ->willReturn($this->object);

        $this->assertEquals($this->object, $manager->getOneBy(['id' => 1]));
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
