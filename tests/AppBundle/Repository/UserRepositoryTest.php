<?php



namespace Tests\AppBundle\Repository;

use AppBundle\Document\User;
use Tests\AppBundle\BaseFunctionalTest;



class UserRepositoryTest extends BaseFunctionalTest
{

    public function testGetUserByUsername()
    {
        $user = new User();
        $user->setUsername("me");
        $userId = $this->userManager->update($user);

        $dbUser = $this->documentManager->getRepository(User::class)->getByUsername("me");

        $this->assertEquals("me", $dbUser->getUsername());
        $this->assertEquals($userId, $dbUser->getId());
    }
}