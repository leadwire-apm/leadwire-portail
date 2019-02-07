<?php

namespace Tests\AppBundle\Provider;

use AppBundle\Document\User;
use Tests\AppBundle\BaseFunctionalTest;
use AppBundle\Provider\SimpleUserProvider;
use ATS\CoreBundle\Service\Util\StringWrapper;


class SimpleUserProviderTest extends BaseFunctionalTest
{
    public function testLoadUserByUsername()
    {

        $user = new User();
        $user->setUuid(StringWrapper::random(32));
        $user->setUsername("me");
        $user->setEmail("me@company.com");
        $this->userManager->update($user);

        $provider = new SimpleUserProvider($this->userManager);

        $user = $provider->loadUserByUsername("me");

        $this->assertEquals("me", $user->getUsername());
        $this->assertEquals("me@company.com", $user->getEmail());
    }
}