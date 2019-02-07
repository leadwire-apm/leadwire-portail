<?php

namespace tests\AppBundle\Document;

use AppBundle\Document\ApplicationType;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class ApplicationTypeTest extends TestCase
{
    public function testGettersSetters()
    {
        $applicationType = new ApplicationType();
        $applicationType
            ->setName("TypeName")
            ->setInstallation("installation")
            ->setTemplate("someTemplate")
            ->setAgent("SomeAgent");

        $this->assertEquals("TypeName", $applicationType->getName());
        $this->assertEquals("installation", $applicationType->getInstallation());
        $this->assertEquals("someTemplate", $applicationType->getTemplate());
        $this->assertEquals("SomeAgent", $applicationType->getAgent());
    }
}
