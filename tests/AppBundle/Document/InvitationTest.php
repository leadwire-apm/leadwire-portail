<?php

namespace tests\AppBundle\Document;

use AppBundle\Document\Application;
use AppBundle\Document\Invitation;
use AppBundle\Document\User;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class InvitationTest extends TestCase
{
    public function testGettersSetters()
    {
        $application = new Application();
        $application->setName("ApplicationName");

        $user = new User();
        $user->setUsername("me");

        $invitation = new Invitation();
        $invitation
            ->setApplication($application)
            ->setEmail("me@company.com")
            ->setPending(true)
            ->setUser($user);

        $this->assertEquals($application->getName(), $invitation->getApplication()->getName());
        $this->assertEquals($user->getUsername(), $invitation->getUser()->getUsername());
        $this->assertEquals(true, $invitation->isPending());
        $this->assertEquals("me@company.com", $invitation->getEmail());
    }
}
