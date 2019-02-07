<?php


namespace Tests\AppBundle\Service;

use Tests\AppBundle\BaseFunctionalTest;
use AppBundle\Service\ApplicationTypeService;

class ApplicationTypeServiceTest extends BaseFunctionalTest
{
    public function testCreateDefaultType()
    {
        $svc = $this->container->get(ApplicationTypeService::class);

        $defaultType = $svc->createDefaultType();

        $this->assertEquals("Java", $defaultType->getName());
    }
}