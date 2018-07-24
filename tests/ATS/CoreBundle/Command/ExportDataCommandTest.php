<?php

namespace Tests\ATS\CoreBundle\Command;

use ATS\CoreBundle\Service\Exporter\Exporter;
use ATS\CoreBundle\Command\Tools\ExportDataCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ExportDataCommandTest extends KernelTestCase
{

    public function testExecute()
    {
        $kernel = self::bootKernel();
        $managerRegistry = $kernel->getContainer()->get('doctrine_mongodb');
        $exporter = new Exporter($managerRegistry);

        $application = new Application($kernel);

        $application->add(new ExportDataCommand($exporter));

        $command = $application->find('ats:core:export-data');
        $commandTester = new CommandTester($command);
        $returnCode = $commandTester->execute(array(
            'command' => $command->getName(),
            'document' => 'ATS\\CoreBundle:Option',
            '--schema' => 'option.key,option.optionValue.stringValue',
            '--format' => 'csv'
        ));

        $this->assertEquals(0, $returnCode);
    }
}
