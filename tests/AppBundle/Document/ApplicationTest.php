<?php

namespace tests\AppBundle\Document;

use AppBundle\Document\Application;
use AppBundle\Document\ApplicationType;
use AppBundle\Document\User;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class ApplicationTest extends TestCase
{
    public function testGettersSetters()
    {
        $application = new Application();

        $applicationType = new ApplicationType();
        $applicationType->setName("BS");

        $owner = new User();
        $owner->setUsername("me");

        $application
            ->setDemo(true)
            ->setDescription("description")
            ->setEmail("me@example.com")
            ->setEnabled(true)
            ->setName("AwesomeApplication")
            ->setOwner($owner)
            ->setPaymentData('someString')
            ->setRemoved(false)
            ->setType($applicationType)
            ->setUuid('SomeWannaBeUniqueString');

        $this->assertEquals(true, $application->isDemo());
        $this->assertEquals(true, $application->isEnabled());
        $this->assertEquals(false, $application->isRemoved());
        $this->assertEquals("description", $application->getDescription());
        $this->assertEquals("me@example.com", $application->getEmail());
        $this->assertEquals("AwesomeApplication", $application->getName());
        $this->assertEquals("someString", $application->getPaymentData());
        $this->assertEquals("SomeWannaBeUniqueString", $application->getUuid());
        $this->assertEquals($owner->getUsername(), $application->getOwner()->getUsername());
        $this->assertEquals($applicationType->getName(), $application->getType()->getName());
    }

    public function testToggleEnabled()
    {
        $application = new Application();
        $application->setEnabled(false);

        $this->assertFalse($application->isEnabled());

        $application->toggleEnabled();
        $this->assertTrue($application->isEnabled());

        $application->toggleEnabled();
        $this->assertFalse($application->isEnabled());
    }
}
