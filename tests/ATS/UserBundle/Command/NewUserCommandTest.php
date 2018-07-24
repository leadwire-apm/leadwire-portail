<?php

namespace Tests\ATS\UserBundle\Command;

use ATS\UserBundle\Command\NewUserCommand;
use ATS\UserBundle\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class NewUserCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $managerRegistry = $kernel->getContainer()->get('doctrine_mongodb');
        $encoder = $kernel->getContainer()->get('security.password_encoder');
        $userManager = new UserManager($managerRegistry, $encoder);
        $encoderFactory = $kernel
            ->getContainer()
            ->get('test_alias.Symfony\Component\Security\Core\Encoder\EncoderFactory');

        $application->add(new NewUserCommand($userManager, $encoderFactory));

        $testUser = $userManager->getUserByUsername('Wouter');

        if ($testUser) {
            $userManager->delete($testUser);
        }

        $command = $application->find('ats:user:create');
        $commandTester = new CommandTester($command);
        $returnCode = $commandTester->execute(array(
            'command' => $command->getName(),
            'username' => 'Wouter',
            'password' => 'Wouter',
        ));

        $output = $commandTester->getDisplay();
        $this->assertEquals(0, $returnCode);
        $this->assertContains('User [Wouter] created successfully.', $output);

        $returnCode = $commandTester->execute(array(
            'command' => $command->getName(),
            'username' => 'Wouter',
            'password' => 'Wouter',
        ));

        $output = $commandTester->getDisplay();
        $this->assertEquals(0, $returnCode);
        $this->assertContains('A user with the username [Wouter] already exists !', $output);

    }
}
