<?php

namespace Tests\ATS\EmailBundle\Document;

use ATS\EmailBundle\Document\Email;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class EmailTest extends TestCase
{
    // public function testEmailConstructor()
    // {

    // }

    public function testGettersSetters()
    {
        $email = new Email();
        $sent = new \DateTime();
        $email->setSubject("Email Subject")
            ->setMessageParameters([])
            ->setSenderAddress("john.doe@gmail.com")
            ->setSenderName("John DOE")
            ->setRecipientAddress("me@example.com")
            ->setSentAt($sent)
            ->setTemplate(null);

        $this->assertSame("Email Subject", $email->getSubject());
        $this->assertSame([], $email->getMessageParameters());
        $this->assertSame("john.doe@gmail.com", $email->getSenderAddress());
        $this->assertSame("John DOE", $email->getSenderName());
        $this->assertSame("me@example.com", $email->getRecipientAddress());
        $this->assertSame($sent, $email->getSentAt());
        $this->assertSame(null, $email->getTemplate());
    }
}
