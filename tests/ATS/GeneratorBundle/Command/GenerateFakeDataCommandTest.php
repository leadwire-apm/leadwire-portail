<?php

namespace Tests\ATS\GeneratorBundle\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use ATS\GeneratorBundle\Command\GenerateFakeDataCommand;

class GenerateFakeDataCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $kernel = self::bootKernel();
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
            'entity' => 'ATS\\CoreBundle:Option'
        ));

        $this->assertEquals(0, $returnCode);
    }
}
