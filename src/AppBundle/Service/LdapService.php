<?php

namespace AppBundle\Service;

use AppBundle\Document\Invitation;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Exception\LdapException;
use Symfony\Component\Ldap\Ldap;

/**
 * Class Ldap Service. Manage Ldap connexion and entries
 * @package AppBundle\Service
 * @author Anis Ksontini <aksontini@ats-digital.com>
 */
class LdapService
{
    /**
     * @var array
     */
    private $settings;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * LdapService constructor.
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->settings = $container->getParameter('ldap');
        $this->logger = $logger;
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
        return $this->saveEntry($entry);
    }

    public function createLdapInvitationEntry(Invitation $invitation)
    {
        $uuid = $invitation->getUser()->getUuid();
        $entry = new Entry(
            "cn={$invitation->getApp()->getName()},ou=Group,dc=leadwire,dc=io",
            [
                "changetype" => "modify",
                "add" =>  "$uuid",
                "memberUid" => $uuid,
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
     * @return bool
     */
    protected function saveEntry(Entry $entry)
    {
        try {
            $ldap = $this->instantiateLdap();
            $entryManager = $ldap->getEntryManager();

            $entryManager->add($entry);
            return true;
        } catch (LdapException $e) {
            if (strpos($e->getMessage(), 'Already exists.') !== false) {
                $this->logger->warning($e->getMessage());
                return true;
            } else {
                $this->logger->error($e->getMessage());
                return false;
            }
        }
    }
}
