<?php

namespace Tests\AppBundle\Service;

use AppBundle\Document\User;
use AppBundle\Service\UserService;
use Tests\AppBundle\BaseFunctionalTest;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


class UserServiceTest extends BaseFunctionalTest
{
    public function testListUsersByRole()
    {
        for ($i = 0; $i < 50; $i++) {
            $user = new User("user$i", "user$i@test.com");
            if ($i < 10) {
                $user->setEnabled(true);
            }
            $this->documentManager->persist($user);
        }
        $this->documentManager->flush();

        $users = $this->userService->listUsersByRole(User::DEFAULT_ROLE);

        $this->assertCount(50, $users);
    }

    // public function testSubscribe()
    // {
    // }

    // public function testGetSubscription()
    // {
    // }

    // public function testGetInvoices()
    // {
    // }

    // public function testUpdateSubscription()
    // {
    // }

    // public function testUpdateCreditCard()
    // {
    // }

    // public function testGetUser()
    // {
    // }

    // public function testGetUsers()
    // {
    // }

    // public function testNewUser()
    // {
    // }

    // public function testUpdateUser()
    // {
    // }

    // public function testDeleteUser()
    // {
    // }

    // public function testSendVerificationEmail()
    // {
    // }


    public function testTestSoftDeleteUser()
    {
        $user = new User();
        $user->setUsername("toto");
        $this->documentManager->persist($user);
        $this->documentManager->flush();

        $status = $this->userService->softDeleteUser($user->getId());

        $this->assertTrue($status);

        $fetched = $this->documentManager->getRepository(User::class)->findOneById($user->getId());

        $this->assertEquals(true, $fetched->isDeleted());
    }

    public function testLockToggle()
    {
        $user = new User();
        $user->setUsername("toto");
        $this->documentManager->persist($user);
        $this->documentManager->flush();

        $status = $this->userService->lockToggle($user->getId(), 'This is some lockMessage');
        $fetched = $this->documentManager->getRepository(User::class)->findOneById($user->getId());

        $this->assertTrue($status);
        $this->assertEquals(true, $fetched->isLocked());

        $status = $this->userService->lockToggle($user->getId(), 'This is some lockMessage');
        $fetched = $this->documentManager->getRepository(User::class)->findOneById($user->getId());
        $this->assertFalse($fetched->isLocked());
    }
}