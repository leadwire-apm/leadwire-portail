<?php

namespace Tests\ATS\EmailBundle\Manager;

use ATS\EmailBundle\Document\Email;
use ATS\EmailBundle\Manager\EmailManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EmailManagerTest extends KernelTestCase
{
    private $documentManager;

    private $managerRegistry;

    protected function setUp()
    {
        $kernel = self::bootKernel();
        $this->managerRegistry = $kernel->getContainer()->get('doctrine_mongodb');
        $this->documentManager = $kernel->getContainer()
            ->get('doctrine_mongodb')
            ->getManager();

        $this->documentManager
            ->getRepository(Email::class)
            ->createQueryBuilder()
            ->remove()
            ->getQuery()
            ->execute();
    }

    public function testUpdate()
    {
        $email = new Email();
        $email->setSubject("Test Email")
            ->setSenderAddress("random@ats-digital.com")
            ->setSenderName("Jean Dupont");

        $emailManager = new EmailManager($this->managerRegistry);
        $emailManager->deleteAll();
        $emailManager->update($email);

        $this->assertCount(1, $this->documentManager->getRepository(Email::class)->findBy([]));

    }

    // public function testpaginate()
    // {

    // }

    public function testDelete()
    {
        $currentCount = count($this->documentManager->getRepository(Email::class)->findBy([]));

        $email = new Email();
        $email->setSubject("Test Email")
            ->setSenderAddress("random@ats-digital.com")
            ->setSenderName("Jean Dupont");

        $emailManager = new EmailManager($this->managerRegistry);

        $emailManager->update($email);

        $this->assertCount($currentCount + 1, $this->documentManager->getRepository(Email::class)->findBy([]));

        $emailManager->delete($email);

        $this->assertCount($currentCount, $this->documentManager->getRepository(Email::class)->findBy([]));
    }

    public function testDeleteById()
    {
        $currentCount = count($this->documentManager->getRepository(Email::class)->findBy([]));

        $email = new Email();
        $email->setSubject("Test Email")
            ->setSenderAddress("random@ats-digital.com")
            ->setSenderName("Jean Dupont");

        $emailManager = new EmailManager($this->managerRegistry);

        $emailManager->update($email);

        $this->assertCount($currentCount + 1, $this->documentManager->getRepository(Email::class)->findBy([]));

        $emailManager->deleteById($email->getId());

        $this->assertCount($currentCount, $this->documentManager->getRepository(Email::class)->findBy([]));
    }

    public function testDeleteAll()
    {
        $emailManager = new EmailManager($this->managerRegistry);
        $emailManager->deleteAll();
        $this->assertCount(0, $this->documentManager->getRepository(Email::class)->findBy([]));

        $email = new Email();
        $email->setSubject("Test Email")
            ->setSenderAddress("random@ats-digital.com")
            ->setSenderName("Jean Dupont");

        $emailManager->update($email);

        $this->assertCount(1, $this->documentManager->getRepository(Email::class)->findBy([]));
        $emailManager->deleteAll();
        $this->assertCount(0, $this->documentManager->getRepository(Email::class)->findBy([]));

    }

    public function testGetAll()
    {
        $emailManager = new EmailManager($this->managerRegistry);
        $emailManager->deleteAll();

        $email = new Email();
        $email->setSubject("Test Email")
            ->setSenderAddress("random@ats-digital.com")
            ->setSenderName("Jean Dupont");

        $emailManager->update($email);

        $this->assertCount(1, $emailManager->getAll());

        $email = new Email();
        $email->setSubject("Another Test Email")
            ->setSenderAddress("random@ats-digital.com")
            ->setSenderName("Jean Dupont");

        $emailManager->update($email);

        $this->assertCount(2, $emailManager->getAll());
        $this->assertEquals("Test Email", $emailManager->getAll()[0]->getSubject());
        $this->assertEquals("Another Test Email", $emailManager->getAll()[1]->getSubject());
    }

    public function testGetBy()
    {
        $emailManager = new EmailManager($this->managerRegistry);
        $emailManager->deleteAll();

        $email = new Email();
        $email->setSubject("Test Email")
            ->setSenderAddress("random@ats-digital.com")
            ->setSenderName("Jean Dupont");

        $emailManager->update($email);
        $emails = $emailManager->getBy(['senderAddress' => "random@ats-digital.com"]);

        $this->assertCount(1, $emails);

        $email = new Email();
        $email->setSubject("Another Test Email")
            ->setSenderAddress("random@ats-digital.com")
            ->setSenderName("Jeanne Doe");
        $emailManager->update($email);
        $emails = $emailManager->getBy(['senderAddress' => "random@ats-digital.com"]);

        $this->assertCount(2, $emails);
    }

    public function testGetOneBy()
    {
        $emailManager = new EmailManager($this->managerRegistry);
        $emailManager->deleteAll();

        $email = new Email();
        $email->setSubject("Test Email")
            ->setSenderAddress("random@ats-digital.com")
            ->setSenderName("Jean Dupont");

        $emailManager->update($email);

        $email = new Email();
        $email->setSubject("Another Test Email")
            ->setSenderAddress("notsorandom@ats-digital.com")
            ->setSenderName("Jeanne Doe");
        $emailManager->update($email);

        $emails = $emailManager->getBy(['senderAddress' => "random@ats-digital.com"]);

        $this->assertCount(1, $emails);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->documentManager->close();
        $this->documentManager = null;
    }
}
