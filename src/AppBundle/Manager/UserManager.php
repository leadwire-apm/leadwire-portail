<?php declare (strict_types = 1);

namespace AppBundle\Manager;

use AppBundle\Document\User;
use ATS\CoreBundle\Manager\AbstractManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

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
        /** @var User $user */
        $user = $this->getDocumentRepository()->findOneBy(['username' => $username]);

        return $user;
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
            ->setIsEmailValid(false);

        $this->update($user);
    }
}
