<?php

namespace Tests\ATS\UserBundle\Document;

use ATS\UserBundle\Document\User;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class UserTest extends TestCase
{

    public function testGettersSetters()
    {
        $user = new User();
        $user
            ->setUsername('testUsername')
            ->setSalt('abcdef')
            ->setActive(true)
            ->setPassword('password')
            ->setRoles([])
            ->setApiKey('some-api-key')
        ;

        $this->assertEquals(true, $user->isAccountNonExpired());

        $user->setExpireAt(new \DateTime('3141-5-2'));
        $this->assertEquals(true, $user->isAccountNonExpired());
        $this->assertEquals('testUsername', $user->getUsername());
        $this->assertEquals('abcdef', $user->getSalt());
        $this->assertEquals(true, $user->getActive());
        $this->assertEquals(true, $user->isEnabled());
        $this->assertEquals('password', $user->getPassword());
        $this->assertEquals('3141-05-02', $user->getExpireAt()->format('Y-m-d'));
        $this->assertEquals('some-api-key', $user->getApiKey());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
        $this->assertEquals(true, $user->isAccountNonLocked());
        $this->assertEquals(true, $user->isCredentialsNonExpired());
        $this->assertEquals('testUsername', (string) $user);

        $user->deactivate();
        $this->assertEquals(false, $user->getActive());

        $user->activate();
        $this->assertEquals(true, $user->getActive());

        $user->promote("ROLE_ADMIN");
        $this->assertCount(2, $user->getRoles());
        $this->assertContains("ROLE_ADMIN", $user->getRoles());

        $user->revoke("ROLE_ADMIN");
        $this->assertCount(1, $user->getRoles());
    }
}
