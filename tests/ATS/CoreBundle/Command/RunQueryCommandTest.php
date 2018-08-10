<?php

namespace Tests\ATS\CoreBundle\Command;

use ATS\CoreBundle\Command\Tools\Doctrine\RunQueryCommand;
use ATS\CoreBundle\Document\Option;
use ATS\CoreBundle\Manager\OptionManager;
use ATS\CoreBundle\Service\Util\StringWrapper;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class RunQueryCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $kernel = self::bootKernel();
        $optionManager = new OptionManager($kernel->getContainer()->get("doctrine_mongodb"));
        $key = StringWrapper::random(32);
        $optionManager->update(new Option($key, "a value"));
        $application = new Application($kernel);

        $application->add(
            new RunQueryCommand(
                $kernel->getContainer()->get('test_alias.Doctrine\Common\Annotations\CachedReader')
            )
        );

        $command = $application->find('ats:core:tools:doctrine:query');
        $commandTester = new CommandTester($command);
        $returnCode = $commandTester->execute(array(
            'command' => $command->getName(),
            'collection' => 'Option',
            '--select' => 'key,type',
            '--where' => 'key=' . $key,
        ));

        $output = $commandTester->getDisplay();
        // $this->assertContains($key, $output);
        $this->assertEquals(0, $returnCode);
    }
}
