<?php declare(strict_types=1);

namespace ATS\UserBundle\Repository;

use ATS\CoreBundle\Repository\BaseDocumentRepository;

/**
 * Repository class for User entities
 *
 * @see \ATS\CoreBundle\Repository\BaseDocumentRepository
 */
class UserRepository extends BaseDocumentRepository
{
    /**
     * Get by usename
     *
     * @param string username
     *
     * @return \ATS\UserBundle\Document\User
     */
    public function getByUsername($username)
    {
        return $this->createQueryBuilder()
            ->field('username')->equals($username)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * Get by apiKey
     *
     * @param string apiKey
     *
     * @return \ATS\UserBundle\Document\User
     */
    public function getByApiKey($apiKey)
    {
        return $this->createQueryBuilder()
            ->field('apiKey')->equals($apiKey)
            ->getQuery()
            ->getSingleResult();
    }
}
