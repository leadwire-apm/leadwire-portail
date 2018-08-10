<?php

namespace Tests\ATS\CoreBundle\Service\Exporter;

use ATS\CoreBundle\Document\Option;
use ATS\CoreBundle\Manager\OptionManager;
use ATS\CoreBundle\Service\Parser\Parser;
use ATS\CoreBundle\Service\Exporter\Exporter;
use ATS\CoreBundle\Service\Parser\CsvStrategy;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use ATS\CoreBundle\Exception\InvalidParserTypeException;
use ATS\GeneratorBundle\Command\GenerateFakeDataCommand;

class ExporterTest extends KernelTestCase
{

    /**
     * @var Exporter
     */
    private $exporter;

    public function setUp()
    {
        $kernel = static::bootKernel();
        $managerRegistry = $kernel->getContainer()->get('doctrine_mongodb');

        $optionManager = new OptionManager($managerRegistry);
        $optionManager->deleteAll();

        $this->exporter = new Exporter($managerRegistry);

        $application = new Application($kernel);

        $application->add(
            new GenerateFakeDataCommand(
                $kernel->getContainer()->get('doctrine_mongodb'),
                $kernel->getContainer()->get('test_alias.annotation.reader')
            )
        );

        $command = $application->find('ats:generator:generate:fake');
        $commandTester = new CommandTester($command);
        $returnCode = $commandTester->execute(array(
            'command' => $command->getName(),
            'entity' => 'ATS\\CoreBundle:Option',
        ));

    }

    public function testExporter()
    {
        $schema = "option.key,option.optionValue.stringValue";

        $this->exporter
            ->setFormat(Exporter::FORMAT_CSV)
            ->setEntity(Option::class)
            ->setFilter(null)
            ->setSchema(explode(',', $schema))
            ->export();

        $data = $this->exporter->getRawData();
        $this->assertCount(10, $data);

        $this->exporter
            ->setFilter("option.key=='123'")
            ->export()
            ;

        $this->assertCount(0, $this->exporter->getRawData());
    }
}
