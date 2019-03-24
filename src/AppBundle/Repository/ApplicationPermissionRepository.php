<?php declare (strict_types = 1);

namespace AppBundle\Repository;

use AppBundle\Document\User;
use AppBundle\Document\Application;
use AppBundle\Document\ApplicationPermission;
use ATS\CoreBundle\Repository\BaseDocumentRepository;

/**
 * Repository class for App entities
 *
 * @see \ATS\CoreBundle\Repository\BaseDocumentRepository
 */
class ApplicationPermissionRepository extends BaseDocumentRepository
{
    public function findUserAccessible(User $user)
    {
        return $this
            ->createQueryBuilder()
            ->eagerCursor(true)
            ->find()
            ->field('access')
            ->notEqual(ApplicationPermission::ACCESS_DENIED)
            ->field('user.id')->equals($user->getId())
            ->getQuery()
            ->execute()
            ->toArray(false);
    }

    public function findGrantedAccessForApplication(Application $application)
    {
        return $this
            ->createQueryBuilder()
            ->eagerCursor(true)
            ->find()
            ->field('application.id')->equals($application->getId())
            ->field('access')->notEqual(ApplicationPermission::ACCESS_DENIED)
            ->getQuery()
            ->execute()
            ->toArray(false);
    }
}
