<?php

namespace Tests\ATS\UserBundle\Command;

use ATS\UserBundle\Command\ChangePasswordCommand;
use ATS\UserBundle\Document\User;
use ATS\UserBundle\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ChangePasswordCommandTest extends KernelTestCase
{

    public function testExecute()
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $managerRegistry = $kernel->getContainer()->get('doctrine_mongodb');
        $encoder = $kernel->getContainer()->get('security.password_encoder');
        $userManager = new UserManager($managerRegistry, $encoder);

        $application->add(
            new ChangePasswordCommand(
                $userManager,
                $kernel->getContainer()->get('test_alias.Symfony\Component\Security\Core\Encoder\EncoderFactory')
            )
        );

        $command = $application->find('ats:user:change-password');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),

            // pass arguments to the helper
            'username' => 'NotFoundUser',
            'password' => 'password',
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
            'password' => 'password',
        ));

        $this->assertEquals(0, $returnCode);
    }
}
