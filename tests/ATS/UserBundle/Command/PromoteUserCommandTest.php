<?php

namespace Tests\ATS\UserBundle\Command;

use ATS\UserBundle\Command\PromoteUserCommand;
use ATS\UserBundle\Document\User;
use ATS\UserBundle\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class PromoteUserCommandTest extends KernelTestCase
{

    public function testExecute()
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $managerRegistry = $kernel->getContainer()->get('doctrine_mongodb');
        $encoder = $kernel->getContainer()->get('security.password_encoder');
        $userManager = new UserManager($managerRegistry, $encoder);

        $application->add(new PromoteUserCommand($userManager));

        $command = $application->find('ats:user:promote');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'username' => 'NotFoundUser',
        ));

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertContains('Unable to find user NotFoundUser', $output);

        $user = $userManager->getUserByUsername('batarang');
        if (!$user) {
            $user = new User();
            $user->setUsername('batarang');
            $userManager->update($user);
        }

        $returnCode = $commandTester->execute(array(
            'command' => $command->getName(),
            'username' => 'batarang',
            'roles' => 'ROLE_ADMIN',
        ));

        $this->assertEquals(0, $returnCode);
    }
}
