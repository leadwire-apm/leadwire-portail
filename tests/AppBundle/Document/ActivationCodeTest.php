<?php

namespace tests\AppBundle\Document;

use AppBundle\Document\ActivationCode;
use AppBundle\Document\Application;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class ActivationCodeTest extends TestCase
{
    public function testGettersSetters()
    {
        $now = new \DateTime();
        $activationCode = new ActivationCode();
        $application = new Application();
        $application->setName("SomeGreateApplication");

        $this->assertEquals(null, $activationCode->getApplication());
        $this->assertEquals(false, $activationCode->isUsed());

        $activationCode->setUsed(true)->setCreatedAt($now)->setCode('Hello There');

        $this->assertEquals($now, $activationCode->getCreatedAt());
        $this->assertEquals('Hello There', $activationCode->getCode());
        $activationCode->setApplication($application);
        $this->assertEquals($application->getName(), $activationCode->getApplication()->getName());
    }
}
