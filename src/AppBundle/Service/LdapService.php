<?php

namespace AppBundle\Service;

use AppBundle\Document\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\Entry;
use AppBundle\Document\Invitation;
use AppBundle\Document\Application;
use AppBundle\Manager\ApplicationManager;
use Symfony\Component\Ldap\Exception\LdapException;
use Symfony\Component\Ldap\Adapter\EntryManagerInterface;

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
     * createInvitationEntry
     *
     * @param Invitation $invitation
     *
     * @return bool
     */
    public function createInvitationEntry(Invitation $invitation): bool
    {
        $appIndex = $invitation->getApplication()->getUuid();
        $userName = self::USER_NAME_PREFIX . $invitation->getUser()->getUuid();

        $appRecord = $this->ldap->query('ou=Group,dc=leadwire,dc=io', "(cn=app_$appIndex)")->execute();
        $entry = $appRecord[0];

        if ($entry instanceof Entry) {
            $oldValue = $entry->getAttribute('member') !== null ? $entry->getAttribute('member') : [];
            $entry->setAttribute('member', array_merge($oldValue, ["cn=$userName,ou=People,dc=leadwire,dc=io"]));
            $this->entryManager->update($entry);
        } else {
            throw new \Exception("Unable to find LDAP records for applications app tenant");
        }

        $sharedRecord = $this->ldap->query('ou=Group,dc=leadwire,dc=io', "(cn=shared_$appIndex)")->execute();

        $entry = $appRecord[0];

        if ($entry instanceof Entry) {
            $oldValue = $entry->getAttribute('member') !== null ? $entry->getAttribute('member') : [];
            $entry->setAttribute('member', array_merge($oldValue, ["cn=$userName,ou=People,dc=leadwire,dc=io"]));
            $this->entryManager->update($entry);
        } else {
            throw new \Exception("Unable to find LDAP records for applications shared tenant");
        }
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
        // TODO Change this to query then add instead of catching exception

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
     * @param User $user
     *
     * @return boolean
     */
    public function createNewUserEntries(User $user): bool
    {
        $allUserTenant = self::ALL_USER_TENANT_PREFIX . $user->getUuid();
        $userName = self::USER_NAME_PREFIX . $user->getUuid();
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

    public function registerApplication(User $user, Application $application): bool
    {
        $userName = self::USER_NAME_PREFIX . $user->getUuid();

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

        return true;
    }
    /**
     * Register demonstration applications for newly created user
     *
     * @param User $user
     *
     * @return void
     */
    public function registerDemoApplications(User $user)
    {
        $demoApplications = $this->applicationManager->getBy(['demo' => true]);

        /** @var Application $application */
        foreach ($demoApplications as $application) {
            $this->registerApplication($user, $application);
        }
    }

    public function createDemoApplicationsEntries()
    {
        $demoApplications = $this->applicationManager->getBy(['demo' => true]);
        foreach ($demoApplications as $application) {
            $this->createApplicationEntry($application);
        }
    }

    /**
     *
     * @param Application $application
     *
     * @return bool
     */
    public function createApplicationEntry(Application $application): bool
    {
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

        $status = $this->saveEntry($entry);

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

        $status = $status && $this->saveEntry($entry);

        return $status;
    }
}
