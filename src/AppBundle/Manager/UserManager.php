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

    /**
     *
     * @param string $username
     * @param string $uuid
     * @param string $avatar
     * @param string $name
     * @param array $roles
     * @param boolean $active
     *
     * @return User
     */
    public function create($username, $uuid, $avatar, $name, $roles = [], $active = true): User
    {
        $user = new User();
        $user
            ->setAvatar($avatar)
            ->setUuid($uuid)
            ->setName($name)
            ->setEmailValid(false)
            ->setUsername($username)
            ->setRoles($roles)
            ->setActive($active)
            ->setPassword("");

        $this->update($user);

        return $user;
    }

    /**
     *
     * @param string $username
     * @param string $uuid
     * @param string $avatar
     * @param string $name
     * @param array $roles
     * @param boolean $active
     * @param string $email
     *
     * @return User
     */
    public function createWithEmail($username, $uuid, $avatar, $name, $roles = [], $active = true, $email): User
    {
        $user = new User();
        $user
            ->setAvatar($avatar)
            ->setUuid($uuid)
            ->setName($name)
            ->setEmailValid(true)
            ->setEmail($email)
            ->setUsername($username)
            ->setRoles($roles)
            ->setActive($active)
            ->setPassword("");

        $this->update($user);

        return $user;
    }
}
