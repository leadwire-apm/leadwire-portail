<?php

namespace Tests\ATS\UserBundle\Manager;

use ATS\UserBundle\Document\User;
use ATS\UserBundle\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserManagerTest extends KernelTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->documentManager = $kernel->getContainer()
            ->get('doctrine_mongodb')
            ->getManager();

        $this->um = new UserManager(
            $kernel->getContainer()->get('doctrine_mongodb'),
            $kernel->getContainer()->get('security.password_encoder')
        );
    }

    public function testGetUserByUsername()
    {
        $this->um->deleteAll();
        $user = new User();
        $user->setUsername('dummy');
        $this->um->update($user);

        $fetched = $this->um->getUserByUsername('dummy');
        $this->assertEquals('dummy', $fetched->getUsername());
        $this->assertEquals($user->getId(), $fetched->getId());
    }

    public function testGetUserByApiKey()
    {
        $this->um->deleteAll();
        $user = new User();
        $user->setUsername('dummy')
            ->setApiKey('thisIsAnApiKey');
        $this->um->update($user);

        $fetched = $this->um->getUserByApiKey('thisIsAnApiKey');

        $this->assertEquals($user->getId(), $fetched->getId());
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->documentManager->close();
        $this->documentManager = null; // avoid memory leaks
    }
}
