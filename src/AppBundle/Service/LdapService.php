<?php

namespace AppBundle\Service;

use AppBundle\Document\Application;
use AppBundle\Document\Invitation;
use AppBundle\Manager\ApplicationManager;
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
    const ALL_USER_TENANT_PREFIX = 'all_user_';
    const USER_NAME_PREFIX = 'user_';

    /**
     * @var array
     */
    private $settings;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ApplicationManager
     */
    private $applicationManager;

    /**
     * LdapService constructor.
     *
     * @param LoggerInterface $logger
     * @param ApplicationManager $applicationManager
     * @param array $settings
     */
    public function __construct(LoggerInterface $logger, ApplicationManager $applicationManager, array $settings)
    {
        $this->settings = $settings;
        $this->applicationManager = $applicationManager;
        $this->logger = $logger;
    }

    /**
     * Create Ldap entry on User Creation
     * @param string $uuid
     *
     */
    public function createUserEntry(string $uuid)
    {
        $this->createUserIndex("user_$uuid");
        $this->createUserIndex("user_all_$uuid");
    }

    /**
     * createUserIndex
     *
     * @param string $userIndex
     * @return void
     */
    public function createUserIndex(string $userIndex)
    {
        $entry = new Entry(
            "cn=$userIndex,ou=People,dc=leadwire,dc=io",
            [
                'gidNumber' => '5',
                'objectClass' => ['posixGroup', 'top'],
            ]
        );

        $this->saveEntry($entry);
    }

    /**
     * createAppEntry
     *
     * @param string $userIndex
     * @param string $appUuid
     * @return bool
     */
    public function createAppEntry(string $userIndex, string $appUuid): bool
    {
        $entryApp = $this->createAppIndex("app_$appUuid", $userIndex);
        $entryShared = $this->createAppIndex("shared_$appUuid", $userIndex);

        return $entryApp && $entryShared;
    }

    /**
     * createInvitationEntry
     *
     * @param Invitation $invitation
     *
     * @return bool
     */
    public function createInvitationEntry(Invitation $invitation): bool
    {
        $uuid = $invitation->getUser()->getUuid();
        $entry = new Entry(
            "cn=app_{$invitation->getApplication()->getUuid()},ou=Group,dc=leadwire,dc=io",
            [
                "changetype" => "modify",
                "add" => "user_$uuid",
                "memberUid" => "user_$uuid",
            ]
        );

        $this->saveEntry($entry);
    }

    /**
     * createAppIndex
     *
     * @param string $index
     * @param string $userIndex
     *
     * @return bool
     */
    public function createAppIndex(string $index, string $userIndex)
    {
        $entry = new Entry(
            "cn=$index,ou=Group,dc=leadwire,dc=io",
            [
                'cn' => "$index",
                'objectClass' => ['groupofnames'],
                'member' => "cn=$userIndex,ou=People,dc=leadwire,dc=io",
            ]
        );
        return $this->saveEntry($entry);
    }

    /**
     * instantiateLdap
     *
     * @return Ldap
     */
    protected function instantiateLdap(): Ldap
    {
        $ldap = Ldap::create(
            'ext_ldap',
            [
                'connection_string' => 'ldap://' . $this->settings['host'] . ':' . $this->settings['port'],
            ]
        );

        $ldap->bind($this->settings['dn_user'], $this->settings['mdp']);

        return $ldap;
    }

    /**
     * Save Ldap entry
     *
     * @param Entry $entry
     *
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

    /**
     * Create LDAP entries for new users
     *
     * @param string $uuid
     *
     * @return boolean
     */
    public function createNewUser(string $uuid): bool
    {
        $allUserTenant = self::ALL_USER_TENANT_PREFIX . $uuid;
        $userName = self::USER_NAME_PREFIX . $uuid;
        $status = true;

        // ALL_USER entry
        $entry = new Entry(
            "dn: cn=$allUserTenant,ou=Group,dc=leadwire,dc=io",
            [
                'cn' => "$allUserTenant",
                'objectClass' => ['groupofnames'],
                'member' => "cn=leadwire-apm,ou=People,dc=leadwire,dc=io",
                'description' => 'appname',
            ]
        );

        $status = $this->saveEntry($entry);

        // People entry
        $entry = new Entry(
            "dn: cn=$userName,ou=People,dc=leadwire,dc=io",
            [
                "cn" => $userName,
                "gidNumber" => " 789",
                "objectclass" => ['posixGroup', 'top'],
                "description" => "username",
            ]
        );

        $status = $status && $this->saveEntry($entry);

        // ADD MEMBER TO ALL_USER GROUP
        $entry = new Entry(
            "dn: cn=$allUserTenant,ou=Group,dc=leadwire,dc=io",
            [
                "changetype" => "modify",
                "add" => "member",
                "member" => "cn=$userName,ou=People,dc=leadwire,dc=io",
            ]
        );

        $status = $status && $this->saveEntry($entry);

        return $status;
    }

    /**
     * Register demonstration applications for newly created user
     *
     * @param string $uuid
     *
     * @return void
     */
    public function registerDemoApplications(string $uuid)
    {
        $userName = self::USER_NAME_PREFIX . $uuid;

        $demoApplications = $this->applicationManager->getBy(['demo' => true]);

        /** @var Application $application */
        foreach ($demoApplications as $application) {
            $entry = new Entry(
                "dn: cn=app_{$application->getUuid()},ou=Group,dc=leadwire,dc=io",
                [
                    "changetype" => "modify",
                    "add" => "member",
                    "member" => "cn=$userName,ou=People,dc=leadwire,dc=io",
                ]
            );
            $this->saveEntry($entry);

            $entry = new Entry(
                "dn: cn=shared_{$application->getUuid()},ou=Group,dc=leadwire,dc=io",
                [
                    "changetype" => "modify",
                    "add" => "member",
                    "member" => "cn=$userName,ou=People,dc=leadwire,dc=io",
                ]
            );
            $this->saveEntry($entry);
        }
    }
}
