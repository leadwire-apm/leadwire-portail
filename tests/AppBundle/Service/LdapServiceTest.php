<?php

namespace Tests\AppBundle\Service;

use Ramsey\Uuid\Uuid;
use AppBundle\Document\User;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\Entry;
use AppBundle\Service\LdapService;
use Tests\AppBundle\BaseFunctionalTest;

class LdapServiceTest extends BaseFunctionalTest
{

    public function testCreateNewUserEntries()
    {
        $uuid = 'ldap_test';
        $ldapSettings = $this->container->getParameter('ldap');

        /** @var LdapService $ldapService */
        $ldapService = $this->container->get(LdapService::class);

        $ldapService->createNewUserEntries($uuid);

        $ldap = Ldap::create(
            'ext_ldap',
            [
                'connection_string' => 'ldap://' . $ldapSettings['host'] . ':' . $ldapSettings['port'],
            ]
        );

        $ldap->bind($ldapSettings['dn_user'], $ldapSettings['mdp']);
        $entryManager = $ldap->getEntryManager();

        $allUserTenant = LdapService::ALL_USER_TENANT_PREFIX . $uuid;
        $userName = LdapService::USER_NAME_PREFIX . $uuid;

        $result = $ldap->query('ou=Group,dc=leadwire,dc=io', "(cn=$allUserTenant)")->execute();
        $entryUserAll = $result[0];
        $this->assertInstanceOf(Entry::class, $entryUserAll);
        $this->assertEquals($entryUserAll->getAttribute('cn')[0], $allUserTenant);
        $this->assertEquals($entryUserAll->getAttribute('member')[0], "cn=leadwire-apm,ou=People,dc=leadwire,dc=io");
        $this->assertEquals($entryUserAll->getAttribute('member')[1], "cn=$userName,ou=People,dc=leadwire,dc=io");

        $result = $ldap->query('ou=People,dc=leadwire,dc=io', "(cn=$userName)")->execute();
        $entryUser = $result[0];
        $this->assertInstanceOf(Entry::class, $entryUser);
        $this->assertEquals($entryUser->getAttribute('cn')[0], $userName);

        $entryManager->remove($entryUserAll);
        $entryManager->remove($entryUser);
    }

    public function testSaveEntry()
    {

    }
}
