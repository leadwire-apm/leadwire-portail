<?php

namespace tests\AppBundle\Document;

use AppBundle\Document\User;
use AppBundle\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Tests\AppBundle\BaseFunctionalTest;

class UserManagerTest extends BaseFunctionalTest
{
    public function testGetUserByUsername()
    {
        $user = new User();
        $user->setUsername("me");
        $this->userManager->update($user);

        $dbUser = $this->userManager->getUserByUsername("me");
        $this->assertEquals("me", $dbUser->getUsername());
    }

    public function testCreate()
    {
        $this->userManager->deleteAll();
        $this->assertCount(0, $this->userManager->getAll());

        $this->userManager->create("me", "uuid", "avatar", "John Doe");

        $users = $this->userManager->getAll();
        $this->assertCount(1, $users);

        $this->assertEquals("me", $users[0]->getUsername());
    }
}
