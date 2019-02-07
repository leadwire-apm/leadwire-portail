<?php

namespace Tests\AppBundle\Service;

use AppBundle\Document\User;
use AppBundle\Service\UserService;
use Tests\AppBundle\BaseFunctionalTest;
use ATS\CoreBundle\Service\Util\StringWrapper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use function GuzzleHttp\json_encode;


class UserServiceTest extends BaseFunctionalTest
{
    public function testListUsersByRole()
    {
        $this->userManager->deleteAll();

        for ($i = 0; $i < 50; $i++) {
            $user = new User("user$i", "user$i@test.com");
            $user->setUuid(StringWrapper::random(32));
            if ($i === 0) {
                $user->setRoles([USer::ROLE_SUPER_ADMIN]);
            }
            if ($i === 1) {
                $user->setRoles([USer::ROLE_ADMIN]);
            }
            if ($i < 10) {
                $user->setActive(true);
            }
            $this->documentManager->persist($user);
        }
        $this->documentManager->flush();

        $users = $this->userService->listUsersByRole('whatever');
        $this->assertCount(50, $users);

        $users = $this->userService->listUsersByRole('admin');
        $this->assertCount(1, $users);
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

    public function testGetUser()
    {
        $this->userManager->deleteAll();
        $user =  new User();
        $user->setUsername('user1');
        $this->documentManager->persist($user);
        $this->documentManager->flush();
        $fetched = $this->userService->getUser($user->getId());

        $this->assertEquals($user->getUsername(), $fetched->getUsername());
    }

    public function testGetUsers()
    {
        $this->userManager->deleteAll();

        for ($i = 0; $i < 50; $i++) {
            $user = new User("user$i", "user$i@test.com");
            $user->setUuid(StringWrapper::random(32));
            if ($i < 10) {
                $user->setActive(true);
            }
            $this->documentManager->persist($user);
        }
        $this->documentManager->flush();

        $users = $this->userService->getUsers();
        $this->assertCount(50, $users);
    }

    public function testNewUser()
    {
        $this->userManager->deleteAll();
        $user =  new User();
        $user->setUsername('user1');

        $json = $this->serializer->serialize($user, 'json');

        $success = $this->userService->newUser($json);

        $this->assertTrue($success);

        $fetched = $this->userManager->getOneBy(['username' => 'user1']);

        $this->assertEquals($fetched->getUsername(), $user->getUsername());

    }

    public function testDeleteUser()
    {
        $this->userManager->deleteAll();
        $user =  new User();
        $user->setUsername('user1');
        $this->documentManager->persist($user);
        $this->documentManager->flush();

        $this->userService->deleteUser($user->getId());
        $users = $this->userManager->getAll();

        $this->assertCount(0, $users);

    }

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