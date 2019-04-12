<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\Application;
use AppBundle\Document\ApplicationPermission;
use AppBundle\Document\User;
use AppBundle\Manager\ApplicationManager;
use AppBundle\Manager\ApplicationPermissionManager;
use AppBundle\Manager\UserManager;
use AppBundle\SearchGuard\SgRoles;
use AppBundle\SearchGuard\SgRolesMapping;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class SearchGuardService
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ApplicationManager
     */
    private $applicationManager;

    /**
     * @var ApplicationPermissionManager
     */
    private $permissionManager;

    /**
     * @var UserManager
     */
    private $userManager;

    private $sgConfig;

    public function __construct(
        SerializerInterface $serializer,
        LoggerInterface $logger,
        ApplicationManager $applicationManager,
        ApplicationPermissionManager $permissionManager,
        UserManager $userManager,
        array $sgConfig
    ) {
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->applicationManager = $applicationManager;
        $this->permissionManager = $permissionManager;
        $this->userManager = $userManager;
        $this->sgConfig = $sgConfig;
    }

    public function prepareMappingsConfig()
    {
        $sgRolesMapping = new SgRolesMapping();
        $allUsers = [];
        $serialized = $this->serializer->serialize($sgRolesMapping, 'yml');

        $applications = $this->applicationManager->getBy(['removed' => false]);

        /** @var Application $application */
        foreach ($applications as $application) {
            $applicationIndex = "sg_{$application->getName()}_index";
            $kibanaIndex = "sg_{$application->getName()}_kibana_index";
            $users = ['adm-portail'];
            $permissions = $this->permissionManager->getGrantedAccessForApplication($application);

            /** @var ApplicationPermission $permission */
            foreach ($permissions as $permission) {
                $users[] = $permission->getUser()->getUserIndex();
                $allUsers[] = $permission->getUser()->getUserIndex();
            }

            $serialized .= $this->serializer->serialize([$applicationIndex => ['users' => $users]], 'yml');
            $serialized .= $this->serializer->serialize([$kibanaIndex => ['users' => $users]], 'yml');
        }

        $allUsers = \array_unique($allUsers);

        foreach ($allUsers as $userIndex) {
            $serialized .= $this->serializer->serialize(["sg_$userIndex" => ['users' => [$userIndex]]], 'yml');
        }

        return $serialized;
    }

    public function prepareConfig()
    {
        $sgRoles = new SgRoles();

        $serialized = $this->serializer->serialize($sgRoles, 'yml');

        $applications = $this->applicationManager->getBy(['removed' => false]);

        foreach ($applications as $application) {
            $serialized .= $this->serializer->serialize(
                [
                    "sg_{$application->getName()}_index" => [
                        'cluster' => ['CLUSTER_COMPOSITE_OPS'],
                        'indices' => [
                            "*-{$application->getName()}-*" => [
                                "*" => ["READ"],
                            ],
                        ],
                    ],
                ],
                'yml'
            );

            $serialized .= $this->serializer->serialize(
                [
                    "sg_{$application->getName()}_kibana_index" => [
                        'cluster' => ['CLUSTER_COMPOSITE_OPS'],
                        'indices' => [
                            "?kibana_{$application->getApplicationIndex()}" => [
                                "*" => [
                                    "READ",
                                    "indices:data/read/get",
                                    "indices:data/read/search",
                                ],
                            ],
                            "?kibana_{$application->getSharedIndex()}" => ["*" => ["INDICES_ALL"]],
                        ],
                    ],
                ],
                'yml'
            );
        }

        $users = $this->userManager->getActiveUsers();
        /** @var User $user */
        foreach ($users as $user) {
            $permissions = $this->permissionManager->getPermissionsForUser($user);

            $indices = [];
            /** @var ApplicationPermission $permission */
            foreach ($permissions as $permission) {
                $indices["*-{$permission->getApplication()->getUuid()}-*"] = [
                    "*" => [
                        "READ",
                        "indices:data/read/field_caps[index]",
                        "indices:data/read/field_caps",
                    ],
                ];
            }

            $serialized .= $this->serializer->serialize(
                [
                    "sg_user_{$user->getUuid()}" => $indices,
                ],
                'yml'
            );
        }

        return $serialized;
    }

    /**
     * * sh /usr/share/elasticsearch/plugins/search-guard-6/tools/sgadmin.sh -cd /usr/share/elasticsearch/plugins/search-guard-6/sgconfig/ -icl -nhnv -cacert /certificates/root-ca.pem -cert /certificates/leadwire-apm.pem -key /certificates/leadwire-apm.key -keypass changeit
     *
     * @return void
     */
    public function updateSearchGuardConfig()
    {
        $fs = new Filesystem();
        $configDir = $this->sgConfig['config_dirpath'];
        $scriptPath = $this->sgConfig['script_path'];

        $sgRolesData = $this->prepareConfig();
        $sgRolesMappingsData = $this->prepareMappingsConfig();

        // Delete previous files if any
        if (is_file($configDir . 'sg_roles.yml') === true) {
            \unlink($configDir . 'sg_roles.yml');
        }
        if (is_file($configDir . 'sg_roles_mapping.yml') === true) {
            \unlink($configDir . 'sg_roles_mapping.yml');
        }

        try {
            $fs->dumpFile($configDir . 'sg_roles.yml', $sgRolesData);
            $fs->dumpFile($configDir . 'sg_roles_mapping.yml', $sgRolesMappingsData);

            // ! Hard coded on purpose
            $output = \shell_exec("sh $scriptPath -cd $configDir -icl -nhnv -cacert /certificates/root-ca.pem -cert /certificates/leadwire-apm.pem -key /certificates/leadwire-apm.key -keypass changeit &");
            $this->logger->notice(
                "leadwire.search_guard.updateSearchGuardConfig",
                [
                    'sg_admin_output' => $output,
                ]
            );
        } catch (IOException $e) {
            $this->logger->critical("leadwire.search_guard.updateSearchGuardConfig", ['error' => $e->getMessage()]);
        }
    }
}
