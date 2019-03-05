<?php declare (strict_types = 1);

namespace AppBundle\Manager;

use AppBundle\Document\User;
use ATS\CoreBundle\Manager\AbstractManager;
use AppBundle\Document\ApplicationPermission;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

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
        return $this
            ->qb()
            ->find()
            ->field('access')
            ->notEqual(ApplicationPermission::ACCESS_DENIED)
            ->field('user.id')->equals($user->getId())
            ->getQuery()
            ->execute()
            ->toArray(false);
    }
}
