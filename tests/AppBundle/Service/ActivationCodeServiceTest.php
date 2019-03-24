<?php


namespace Tests\AppBundle\Service;

use AppBundle\Document\ActivationCode;
use Tests\AppBundle\BaseFunctionalTest;
use AppBundle\Service\ActivationCodeService;


class ActivationCodeServiceTest extends BaseFunctionalTest
{

    public function testGenerateNewCode()
    {
        $activationCodeService = $this->container->get(ActivationCodeService::class);

        /** @var ActivationCode $code */
        $code = $activationCodeService->generateNewCode();

        $this->assertRegExp("/[A-Z0-9]B[A-Z0-9]{2}7[A-Z0-9]/", $code->getCode());
    }

    public function testValidateActivationCode()
    {
        $activationCodeService = $this->container->get(ActivationCodeService::class);
        /** @var ActivationCode $code */
        $code = $activationCodeService->generateNewCode();

        $valid = $activationCodeService->validateActivationCode($code);

        $this->assertTrue($valid);

        $code->setUsed(true);

        $valid = $activationCodeService->validateActivationCode($code);
        $this->assertFalse($valid);
    }
}