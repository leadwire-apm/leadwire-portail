<?php declare (strict_types = 1);

namespace AppBundle\Manager;

use AppBundle\Document\User;
use ATS\CoreBundle\Manager\AbstractManager;
use AppBundle\Document\ApplicationPermission;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use AppBundle\Document\Application;

/**
 * Manager class for App entities
 *
 * @see \ATS\CoreBundle\Manager\AbstractManager
 */
class ApplicationPermissionManager extends AbstractManager
{
    /**
     * @codeCoverageIgnore
     *
     * @param ManagerRegistry $managerRegistry
     * @param ?string $managerName
     */
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, ApplicationPermission::class, $managerName);
    }

    public function getPermissionsForUser(User $user)
    {
        return $this->getDocumentRepository()->findUserAccessible($user);
    }

    public function getGrantedAccessForApplication(Application $application)
    {
        return $this->getDocumentRepository()->findGrantedAccessForApplication($application);
    }

    public function getAccessibleApplications(User $user)
    {
        $applications = [];
        $grantedPermission = $this->getDocumentRepository()->findUserAccessible($user);

        foreach ($grantedPermission as $permission) {
            $applications[] = $permission->getApplication();
        }

        return $applications;
    }
}
