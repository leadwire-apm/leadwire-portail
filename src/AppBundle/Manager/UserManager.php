<?php declare(strict_types=1);

namespace AppBundle\Manager;

use ATS\CoreBundle\Manager\AbstractManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use AppBundle\Document\User;

/**
 * Manager class for User entities
 *
 * @see \ATS\CoreBundle\Manager\AbstractManager
 */
class UserManager extends AbstractManager
{
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, User::class, $managerName);
    }

    /**
     * Get user by its usename
     *
     * @param string $username
     *
     * @return User
     */
    public function getUserByUsername($username)
    {
        return $this->getDocumentRepository()->getByUsername($username);
    }

    public function create($username, $uuid, $avatar, $name, $roles = [], $active = true)
    {
        $user = (new User)
            ->setActive($active)
            ->setUsername($username)
            ->setRoles($roles)
            ->setPassword("")
            ->setUuid($uuid)
            ->setAvatar($avatar)
            ->setName($name)
        ;


        $this->update($user);
    }
}
