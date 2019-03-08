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

class SearchGuardService
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

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

    public function __construct(
        SerializerInterface $serializer,
        ApplicationManager $applicationManager,
        ApplicationPermissionManager $permissionManager,
        UserManager $userManager
    ) {
        $this->serializer = $serializer;
        $this->applicationManager = $applicationManager;
        $this->permissionManager = $permissionManager;
        $this->userManager = $userManager;
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
                $users[] = $permission->getUser()->getIndex();
                $allUsers[] = $permission->getUser()->getIndex();
            }

            $serialized .= $this->serializer->serialize([$applicationIndex => ['users' => $users]], 'yml');
            $serialized .= $this->serializer->serialize([$kibanaIndex => ['users' => $users]], 'yml');
        }

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
                            "?kibana_app_{$application->getUuid()}" => [
                                "*" => [
                                    "READ",
                                    "indices:data/write/index",
                                    "indices:data/write/update",
                                    "indices:data/write/bulk[s]",
                                ],
                            ],
                            "?kibana_shared_{$application->getUuid()}" => ["*" => ["INDICES_ALL"]],
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
                    ]
                ];
            }

            $serialized .= $this->serializer->serialize(
                [
                    "sg_user_{$user->getUuid()}" => $indices
                ],
                'yml'
            );
        }

        return $serialized;
    }
}
