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

    /**
     * Create Ldap entry on User Creation
     * @param string $uuid
     */
    public function createUserEntry(string $uuid)
    {
        $entry = new Entry(
            "cn=user_$uuid,ou=People,dc=leadwire,dc=io",
            [
                'gidNumber' => '5',
                'objectClass' => ['posixGroup', 'top'],
            ]
        );

        $this->saveEntry($entry);
    }

    public function createAppEntry(string $userIndex, string $appUuid)
    {

        $entryApp = $this->createAppIndex("app_$appUuid", $userIndex);
        $entryShared = $this->createAppIndex("shared_$appUuid", $userIndex);

        return $entryApp && $entryShared;
    }

    public function createInvitationEntry(Invitation $invitation)
    {
        $uuid = $invitation->getUser()->getUuid();
        $entry = new Entry(
            "cn=app_{$invitation->getApp()->getUuid()},ou=Group,dc=leadwire,dc=io",
            [
                "changetype" => "modify",
                "add" =>  "user_$uuid",
                "memberUid" => "user_$uuid",
            ]
        );

        $this->saveEntry($entry);
    }

    public function createAppIndex(string $index, string $userIndex)
    {
        $entry = new Entry(
            "cn=$index,ou=Group,dc=leadwire,dc=io",
            [
                'cn' => "$index",
                'objectClass' => ['groupofnames'],
                'member' => "cn=$userIndex,ou=People,dc=leadwire,dc=io"
            ]
        );
        return $this->saveEntry($entry);
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
                $this->logger->critical($e->getMessage());
                return false;
            }
        }
    }
}
