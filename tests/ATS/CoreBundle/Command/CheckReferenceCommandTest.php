<?php

namespace Tests\ATS\CoreBundle\Command;

use ATS\CoreBundle\Command\Tools\Doctrine\CheckReferenceCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CheckReferenceCommandTest extends KernelTestCase
{

    public function testExecute()
    {
        $kernel = self::bootKernel();
        $managerRegistry = $kernel->getContainer()->get('doctrine_mongodb');
        $application = new Application($kernel);

        $application->add(new CheckReferenceCommand($managerRegistry));

        $command = $application->find('ats:core:tools:doctrine:check-reference');
        $commandTester = new CommandTester($command);
        $returnCode = $commandTester->execute(array(
            'command' => $command->getName(),
        ));

        $this->assertEquals(0, $returnCode);
    }
}
