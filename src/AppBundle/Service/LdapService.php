<?php

namespace AppBundle\Service;

use AppBundle\Document\Application;
use AppBundle\Document\Invitation;
use AppBundle\Manager\ApplicationManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Ldap\Adapter\EntryManagerInterface;
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
     * @var Ldap
     */
    private $ldap;

    /**
     * @var EntryManagerInterface
     */
    private $entryManager;

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

        $this->ldap = Ldap::create(
            'ext_ldap',
            [
                'connection_string' => 'ldap://' . $this->settings['host'] . ':' . $this->settings['port'],
            ]
        );

        $this->ldap->bind($this->settings['dn_user'], $this->settings['mdp']);

        $this->entryManager = $this->ldap->getEntryManager();
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
     * Save Ldap entry
     *
     * @param Entry $entry
     *
     * @return bool
     */
    protected function saveEntry(Entry $entry)
    {
        try {
            $this->entryManager->add($entry);

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
    public function createNewUserEntries(string $uuid): bool
    {
        $allUserTenant = self::ALL_USER_TENANT_PREFIX . $uuid;
        $userName = self::USER_NAME_PREFIX . $uuid;
        $status = true;

        // People entry
        $entry = new Entry(
            "cn=$userName,ou=People,dc=leadwire,dc=io",
            [
                "cn" => $userName,
                "gidNumber" => "789",
                "objectclass" => ['posixGroup', 'top'],
                "description" => "username",
            ]
        );

        $status = $this->saveEntry($entry);

        // ALL_USER entry
        $entry = new Entry(
            "cn=$allUserTenant,ou=Group,dc=leadwire,dc=io",
            [
                'cn' => "$allUserTenant",
                'objectClass' => ['groupofnames'],
                'member' => [
                    "cn=leadwire-apm,ou=People,dc=leadwire,dc=io",
                    "cn=$userName,ou=People,dc=leadwire,dc=io",
                ],
                'description' => 'appname',
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
            $result = $this->ldap->query('ou=Group,dc=leadwire,dc=io', "(cn=app_{$application->getUuid()})")->execute();
            $entry = $result[0];
            if ($entry instanceof Entry) {
                $oldValue = $entry->getAttribute('member') !== null ? $entry->getAttribute('member') : [];
                $entry->setAttribute('member', array_merge($oldValue, ["cn=$userName,ou=People,dc=leadwire,dc=io"]));
                $this->entryManager->update($entry);
            } else {
                throw new \Exception("Unable to find LDAP records for demo applications app tenant");
            }

            $result = $this->ldap->query('ou=Group,dc=leadwire,dc=io', "(cn=shared_{$application->getUuid()})")->execute();

            $entry = $result[0];
            if ($entry instanceof Entry) {
                $oldValue = $entry->getAttribute('member') !== null ? $entry->getAttribute('member') : [];
                $entry->setAttribute('member', array_merge($oldValue, ["cn=$userName,ou=People,dc=leadwire,dc=io"]));
                $this->entryManager->update($entry);
            } else {
                throw new \Exception("Unable to find LDAP records for demo applications shared tenant");
            }
        }
    }

    public function createDemoApplicationsEntries()
    {
        $demoApplications = $this->applicationManager->getBy(['demo' => true]);
        foreach ($demoApplications as $application) {
            // app_ tenant
            $entry = new Entry(
                "cn=app_{$application->getUuid()},ou=Group,dc=leadwire,dc=io",
                [
                    "objectClass" => "groupofnames",
                    "cn" => "app_{$application->getUuid()}",
                    "member" => "cn=leadwire-apm,ou=People,dc=leadwire,dc=io",
                    "description" => "appname",
                ]
            );

            $this->saveEntry($entry);

            // shared_ tenant
            $entry = new Entry(
                "cn=shared_{$application->getUuid()},ou=Group,dc=leadwire,dc=io",
                [
                    "objectClass" => "groupofnames",
                    "cn" => "shared_{$application->getUuid()}",
                    "member" => "cn=leadwire-apm,ou=People,dc=leadwire,dc=io",
                    "description" => "appname",
                ]
            );

            $this->saveEntry($entry);
        }
    }
}
