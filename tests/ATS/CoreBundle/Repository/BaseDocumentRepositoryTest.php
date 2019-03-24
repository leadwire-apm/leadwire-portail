<?php

namespace Tests\ATS\CoreBundle\Controller;

use AppBundle\Document\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use ATS\CoreBundle\Service\Util\AString;

class BaseDocumentRepositoryTest extends WebTestCase
{
    protected function setUp()
    {
        $kernel = self::bootKernel();
        $this->managerRegistry = $kernel->getContainer()->get('doctrine_mongodb');
        $this->documentManager = $kernel->getContainer()
            ->get('doctrine_mongodb')
            ->getManager();
    }

    public function testsave()
    {
        $user = new User();
        $user->setUsername("test_username");
        $user->setEmail("user@test.com");
        $user->setUuid(AString::random(32));
        $this->documentManager->getRepository(User::class)->save($user);
        $fetched = $this->documentManager->getRepository(User::class)->findOneBy(['username' => 'test_username']);
        $this->assertEquals($fetched->getEmail(), 'user@test.com');
    }

    public function testDelete()
    {
        $this->documentManager->getRepository(User::class)->deleteAll();
        $user = new User();
        $user->setUsername("test_username");
        $user->setEmail("user@test.com");
        $user->setUuid(AString::random(32));
        $this->documentManager->getRepository(User::class)->save($user);
        $fetched = $this->documentManager->getRepository(User::class)->findOneBy(['username' => 'test_username']);
        $this->assertEquals($fetched->getEmail(), 'user@test.com');
        $this->documentManager->getRepository(User::class)->delete($user);

        $all = $this->documentManager->getRepository(User::class)->findAll();

        $this->assertCount(0, $all);
    }

}
