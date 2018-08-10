<?php

namespace Tests\ATS\EmailBundle\Service;

use ATS\EmailBundle\Document\Email;
use ATS\EmailBundle\Manager\EmailManager;
use ATS\EmailBundle\Service\SimpleMailerService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SimpleMailerServiceTest extends KernelTestCase
{

    protected function setUp()
    {
        $kernel = self::bootKernel();
        $this->managerRegistry = $kernel->getContainer()->get('doctrine_mongodb');
        $this->container = $kernel->getContainer();

    }

    public function testSend()
    {
        $emailManager = new EmailManager($this->managerRegistry);

        $mailerService = new SimpleMailerService(
            $this->container->get('swiftmailer.mailer.default'),
            $this->container->get('templating'),
            $emailManager
        );

        $email = new Email();
        $email->setSubject("Hello There")
               ;
        $mailerService->send($email);

        // Test that email is saved
        $mailerService->send($email, true);
        $email = $emailManager->getOneBy(['subject' => 'Hello There']);

        $this->assertEquals('Hello There', $email->getSubject());
    }

    protected function tearDown()
    {
        parent::tearDown();
    }
}
