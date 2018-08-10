<?php

namespace Tests\ATS\EmailBundle\Manager;

use ATS\CoreBundle\Document\Option;
use ATS\CoreBundle\Manager\OptionManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class OptionManagerTest extends KernelTestCase
{
    private $documentManager;

    private $managerRegistry;

    protected function setUp()
    {
        $kernel = self::bootKernel();
        $this->managerRegistry = $kernel->getContainer()->get('doctrine_mongodb');
        $this->documentManager = $kernel->getContainer()
            ->get('doctrine_mongodb')
            ->getManager();

        $this->documentManager
            ->getRepository(Option::class)
            ->createQueryBuilder()
            ->remove()
            ->getQuery()
            ->execute();
    }

    public function testUpdate()
    {
        $option = new Option('testOptionKey', 'stringValue');

        $optionManager = new OptionManager($this->managerRegistry);
        $optionManager->deleteAll();
        $optionManager->update($option);

        $this->assertCount(1, $this->documentManager->getRepository(Option::class)->findBy([]));

    }

    // public function testpaginate()
    // {

    // }

    public function testDelete()
    {
        $currentCount = count($this->documentManager->getRepository(Option::class)->findBy([]));

        $option = new Option('deleteOptionKey', 'stringValue');

        $optionManager = new OptionManager($this->managerRegistry);

        $optionManager->update($option);

        $this->assertCount($currentCount + 1, $this->documentManager->getRepository(Option::class)->findBy([]));

        $optionManager->delete($option);

        $this->assertCount($currentCount, $this->documentManager->getRepository(Option::class)->findBy([]));
    }

    public function testDeleteById()
    {
        $currentCount = count($this->documentManager->getRepository(Option::class)->findBy([]));

        $option = new Option('deleteById', 'stringValue');

        $optionManager = new OptionManager($this->managerRegistry);

        $optionManager->update($option);

        $this->assertCount($currentCount + 1, $this->documentManager->getRepository(Option::class)->findBy([]));

        $optionManager->deleteById($option->getId());

        $this->assertCount($currentCount, $this->documentManager->getRepository(Option::class)->findBy([]));
    }

    public function testDeleteAll()
    {
        $optionManager = new OptionManager($this->managerRegistry);
        $optionManager->deleteAll();
        $this->assertCount(0, $this->documentManager->getRepository(Option::class)->findBy([]));

        $option = new Option('optionKey', 'stringValue');

        $optionManager->update($option);

        $this->assertCount(1, $this->documentManager->getRepository(Option::class)->findBy([]));
        $optionManager->deleteAll();
        $this->assertCount(0, $this->documentManager->getRepository(Option::class)->findBy([]));

    }

    public function testGetAll()
    {
        $optionManager = new OptionManager($this->managerRegistry);
        $optionManager->deleteAll();

        $option = new Option('optionKey', 'stringValue');

        $optionManager->update($option);

        $this->assertCount(1, $optionManager->getAll());

        $option = new Option('second.optionKey', 'stringValue');

        $optionManager->update($option);

        $this->assertCount(2, $optionManager->getAll());
        $this->assertEquals("optionKey", $optionManager->getAll()[0]->getKey());
        $this->assertEquals("second.optionKey", $optionManager->getAll()[1]->getKey());
    }

    public function testGetBy()
    {
        $optionManager = new OptionManager($this->managerRegistry);
        $optionManager->deleteAll();

        $option = new Option('optionKeyGetBy', 'stringValue');

        $optionManager->update($option);
        $options = $optionManager->getBy(['key' => "optionKeyGetBy"]);

        $this->assertCount(1, $options);
    }

    public function testGetOneBy()
    {
        $optionManager = new OptionManager($this->managerRegistry);
        $optionManager->deleteAll();

        $option = new Option('optionKey', 'stringValue');

        $optionManager->update($option);

        $option = new Option('anotherOptionKey', 'stringValue');

        $optionManager->update($option);

        $options = $optionManager->getBy(['key' => "anotherOptionKey"]);

        $this->assertCount(1, $options);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->documentManager->close();
        $this->documentManager = null;
    }
}
