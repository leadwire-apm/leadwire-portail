<?php

namespace AppBundle\Service;

use Psr\Container\ContainerInterface;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Ldap;

/**
 * Class Ldap Service. Manage Ldap connexion and entries
 * @package AppBundle\Service
 * @author Anis Ksontini <aksontini@ats-digital.com
 */
class LdapService
{
    private $settings;

    public function __construct(ContainerInterface $container)
    {
        $this->settings= $container->getParameter('ldap');
    }

    public function createLdapUserEntry(string $username)
    {
        $entry = new Entry(
            "cn=$username,ou=People,dc=leadwire,dc=io",
            [
                'gidNumber' => '5',
                'objectClass' => ['posixGroup', 'top'],
            ]
        );

        $this->saveEntry($entry);
    }

    public function createLdapAppEntry(string $username, string $appName)
    {

        $entry = new Entry(
            "cn=$appName,ou=Group,dc=leadwire,dc=io",
            [
                'cn' => $appName,
                'objectClass' => ['groupofnames'],
                'member' => "cn=$username,ou=People,dc=leadwire,dc=io"
            ]
        );

        $this->saveEntry($entry);
    }

    protected function instantiateLdap()
    {

        $ldap = Ldap::create('ext_ldap', [
            'connection_string' => 'ldap://' . $this->settings['host'] . ':' . $this->settings['port'],
        ]);

        $ldap->bind($this->settings['dn_user'], $this->settings['mdp']);

        return $ldap;
    }

    /**
     * Save Ldap entry
     * @param Entry $entry
     */
    protected function saveEntry(Entry $entry)
    {
        $ldap = $this->instantiateLdap();
        $entryManager = $ldap->getEntryManager();

        $entryManager->add($entry);
    }
}
