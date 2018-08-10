<?php

namespace Tests\ATS\EmailBundle\Repository;

use ATS\EmailBundle\Document\Email;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EmailRepositoryTest extends KernelTestCase
{

    /**
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();
        $this->documentManager = $kernel->getContainer()
            ->get('doctrine_mongodb')
            ->getManager();
    }

    public function testSave()
    {
        $currentCount = count($this->documentManager->getRepository(Email::class)->findAll());

        $email = new Email();
        $email
            ->setSubject("Test Email")
            ->setSenderAddress("random@ats-digital.com")
            ->setSenderName("Jean Dupont");
        $this->documentManager->getRepository(Email::class)->save($email);

        $this->assertCount($currentCount + 1, $this->documentManager->getRepository(Email::class)->findAll());
    }

    public function testDelete()
    {
        $currentCount = count($this->documentManager->getRepository(Email::class)->findAll());

        $this->assertCount($currentCount, $this->documentManager->getRepository(Email::class)->findAll());

        $email = $this->documentManager->getRepository(Email::class)->findOneBy(['subject' => "Test Email"]);

        $this->documentManager->getRepository(Email::class)->delete($email);
        $this->assertCount(max(0, $currentCount - 1), $this->documentManager->getRepository(Email::class)->findAll());
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
