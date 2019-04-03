<?php

namespace AppBundle\Service;

use AppBundle\Document\Application;
use AppBundle\Document\Invitation;
use AppBundle\Document\User;
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
     * @var string
     */
    private $kibanaAdminUsername;

    /**
     * LdapService constructor.
     *
     * @param LoggerInterface $logger
     * @param ApplicationManager $applicationManager
     * @param array $settings
     * @param string $kibanaAdminUsername,
     */
    public function __construct(
        LoggerInterface $logger,
        ApplicationManager $applicationManager,
        array $settings,
        string $kibanaAdminUsername
    ) {
        $this->settings = $settings;
        $this->applicationManager = $applicationManager;
        $this->logger = $logger;
        $this->kibanaAdminUsername = $kibanaAdminUsername;

        try {
            $this->ldap = Ldap::create(
                'ext_ldap',
                [
                    'connection_string' => 'ldap://' . $this->settings['host'] . ':' . $this->settings['port'],
                ]
            );

            $this->ldap->bind($this->settings['dn_user'], $this->settings['mdp']);

            $this->entryManager = $this->ldap->getEntryManager();
        } catch (\Exception $e) {
            $this->logger->emergency('leadwire.ldap.__construct', ['error' => $e->getMessage()]);
        }
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
        $application = $invitation->getApplication();
        $user = $invitation->getUser();

        $appRecord = $this->ldap->query('ou=Group,dc=leadwire,dc=io', "(cn={$application->getApplicationIndex()})")->execute();
        $entry = $appRecord[0];

        if ($entry instanceof Entry) {
            $oldValue = $entry->getAttribute('member') !== null ? $entry->getAttribute('member') : [];
            $entry->setAttribute('member', array_merge($oldValue, ["cn={$user->getUserIndex()},ou=People,dc=leadwire,dc=io"]));
            $this->entryManager->update($entry);
        } else {
            throw new \Exception("Unable to find LDAP records for applications app tenant");
        }

        $sharedRecord = $this->ldap->query('ou=Group,dc=leadwire,dc=io', "(cn={$application->getSharedIndex()})")->execute();

        $entry = $sharedRecord[0];

        if ($entry instanceof Entry) {
            $oldValue = $entry->getAttribute('member') !== null ? $entry->getAttribute('member') : [];
            $entry->setAttribute('member', array_merge($oldValue, ["cn={$user->getUserIndex()},ou=People,dc=leadwire,dc=io"]));
            $this->entryManager->update($entry);
        } else {
            throw new \Exception("Unable to find LDAP records for applications shared tenant");
        }

        return true;
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
        $status = true;

        // People entry
        $entry = new Entry(
            "cn={$user->getUserIndex()},ou=People,dc=leadwire,dc=io",
            [
                "cn" => $user->getUserIndex(),
                "gidNumber" => "789",
                "objectclass" => ['posixGroup', 'top'],
                "description" => $user->getUsername(),
            ]
        );

        $status = $this->saveEntry($entry);

        // ALL_USER entry
        $entry = new Entry(
            "cn={$user->getAllUserIndex()},ou=Group,dc=leadwire,dc=io",
            [
                'cn' => "{$user->getAllUserIndex()}",
                'objectClass' => ['groupofnames'],
                'member' => [
                    "cn={$this->kibanaAdminUsername},ou=People,dc=leadwire,dc=io",
                    "cn={$user->getUserIndex()},ou=People,dc=leadwire,dc=io",
                ],
                'description' => $user->getUsername(),
            ]
        );

        $status = $status && $this->saveEntry($entry);

        return $status;
    }

    public function registerApplication(User $user, Application $application): bool
    {
        foreach (['app_', 'shared_'] as $tenantPrefix) {
            $result = $this->ldap->query('ou=Group,dc=leadwire,dc=io', "(cn={$tenantPrefix}{$application->getUuid()})")->execute();
            $entry = $result[0];
            if ($entry instanceof Entry) {
                $oldValue = $entry->getAttribute('member') !== null ? $entry->getAttribute('member') : [];
                if (in_array("cn={$user->getUserIndex()},ou=People,dc=leadwire,dc=io", $oldValue) === false) {
                    $entry->setAttribute('member', array_merge($oldValue, ["cn={$user->getUserIndex()},ou=People,dc=leadwire,dc=io"]));
                    $this->entryManager->update($entry);
                } else {
                    $this->logger->notice("Entry already up to date [cn={$user->getUserIndex()},ou=People,dc=leadwire,dc=io] in [cn={$application->getApplicationIndex()}]");
                }
            } else {
                $this->logger->critical("Unable to find LDAP records for demo application {$application->getName()}");
                throw new \Exception("Unable to find LDAP records for demo application {$application->getName()}");
            }
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
            "cn={$application->getApplicationIndex()},ou=Group,dc=leadwire,dc=io",
            [
                "objectClass" => "groupofnames",
                "cn" => $application->getApplicationIndex(),
                "member" => "cn={$this->kibanaAdminUsername},ou=People,dc=leadwire,dc=io",
                "description" => $application->getName(),
            ]
        );

        $status = $this->saveEntry($entry);

        // shared_ tenant
        $entry = new Entry(
            "cn={$application->getSharedIndex()},ou=Group,dc=leadwire,dc=io",
            [
                "objectClass" => "groupofnames",
                "cn" => $application->getSharedIndex(),
                "member" => "cn={$this->kibanaAdminUsername},ou=People,dc=leadwire,dc=io",
                "description" => $application->getName(),
            ]
        );

        $status = $status && $this->saveEntry($entry);

        return $status;
    }
}
