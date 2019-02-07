<?php

namespace tests\AppBundle\Document;

use AppBundle\Document\User;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class UserTest extends TestCase
{
    public function testGettersSetters()
    {
        $now = new \DateTime();
        $user = new User();
        $user
        ->setUuid("uuid")
        ->setUsername("me")
        ->setSalt("someSalt")
        ->setActive(true)
        ->setPassword('p4$$w0rd')
        ->setRoles([])
        ->setExpireAt($now)
        ->setName("John Doe")
        ->setAvatar("avatar")
        ->setEmail("me@company.com")
        ->setAcceptNewsLetter(true)
        ->setEmailValid(true)
        ->setContact("someContact")
        ->setContactPreference("email")
        ->setSubscriptionId('id')
        ->setDefaultApplication(null)
        ->setCompany(null)
        ->setCustomer(null)
        ->setPlan(null)
        ->setDeleted(false)
        ->setLocked(false)
        ->setLockMessage('Default lock message');

        $this->assertTrue($user->getAcceptNewsLetter());
        $this->assertTrue($user->isEmailValid());
        $this->assertTrue($user->getActive());

        $this->assertFalse($user->hasRole(User::DEFAULT_ROLE));
        $this->assertFalse($user->isDeleted());
        $this->assertFalse($user->isLocked());
        $this->assertFalse($user->hasRole(User::ROLE_SUPER_ADMIN));

        $this->assertEquals([User::DEFAULT_ROLE], $user->getRoles());
        $this->assertEquals($now, $user->getExpireAt());
        $this->assertEquals("me", $user->getUsername());
        $this->assertEquals("me", $user->getLogin());
        $this->assertEquals("avatar", $user->getAvatar());
        $this->assertEquals("someSalt", $user->getSalt());
        $this->assertEquals('p4$$w0rd', $user->getPassword());
        $this->assertEquals("John Doe", $user->getName());
        $this->assertEquals("me@company.com", $user->getEmail());
        $this->assertEquals("Default lock message", $user->getLockMessage());
        $this->assertEquals("someContact", $user->getContact());
        $this->assertEquals("email", $user->getContactPreference());
        $this->assertEquals("id", $user->getSubscriptionId());
        $this->assertEquals("uuid", $user->getUuid());

        $this->assertNull($user->getDefaultApplication());
        $this->assertNull($user->getCompany());
        $this->assertNull($user->getCustomer());
        $this->assertNull($user->getPlan());

        $user->promote(User::ROLE_ADMIN);
        $this->assertTrue($user->hasRole(User::ROLE_ADMIN));
        $user->revoke(User::ROLE_ADMIN);
        $this->assertFalse($user->hasRole(User::ROLE_ADMIN));

        $user->deactivate();
        $this->assertFalse($user->getActive());
        $user->activate();
        $this->assertTrue($user->getActive());
    }
}
