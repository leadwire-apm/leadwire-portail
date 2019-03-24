<?php

namespace Tests\ATS\CoreBundle\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use ATS\CoreBundle\Command\Tools\Doctrine\CheckReferenceCommand;

class CheckReferenceCommandTest extends WebTestCase
{

    public function testExecute()
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $logger = $kernel->getContainer()->get('logger');
        $managerRegistry = $kernel->getContainer()->get('doctrine_mongodb');
        $application->add((new CheckReferenceCommand($managerRegistry)));

        $command = $application->find('ats:core:tools:doctrine:check-reference');
        $commandTester = new CommandTester($command);
        $returnCode = $commandTester->execute(array(
            'command' => $command->getName(),
        ));

        $this->assertEquals(0, $returnCode);
    }
}
