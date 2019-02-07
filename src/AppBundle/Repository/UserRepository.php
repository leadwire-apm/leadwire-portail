<?php declare (strict_types = 1);

namespace AppBundle\Repository;

use AppBundle\Document\User;
use ATS\CoreBundle\Repository\BaseDocumentRepository;

/**
 * Repository class for User entities
 */
class UserRepository extends BaseDocumentRepository
{
    /**
     * Get by usename
     *
     * @param string $username
     *
     * @return User
     */
    public function getByUsername($username)
    {
        /** @var User $user */
        $user = $this->createQueryBuilder()
            ->field('username')->equals($username)
            ->getQuery()
            ->getSingleResult();

        return $user;
    }
}
