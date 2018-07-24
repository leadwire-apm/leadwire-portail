<?php declare(strict_types=1);

namespace ATS\UserBundle\Manager;

use ATS\CoreBundle\Manager\AbstractManager;
use ATS\UserBundle\Document\User;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Manager class for User entities
 *
 * @see \ATS\CoreBundle\Manager\AbstractManager
 */
class UserManager extends AbstractManager
{

    /**
     * @var UserPasswordEncoderInterface
     **/
    private $passwordEncoder;

    /**
     * Constructor
     *
     * @param ManagerRegistry $managerRegistry
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param string          $managerName
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        UserPasswordEncoderInterface $passwordEncoder,
        $managerName = null
    ) {
        parent::__construct($managerRegistry, User::class, $managerName);
        $this->passwordEncoder = $passwordEncoder;
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

    /**
     * Get user by its registered API key
     *
     * @param string $apiKey
     *
     * @return User
     */
    public function getUserByApiKey($apiKey)
    {
        return $this->getDocumentRepository()->getByApiKey($apiKey);
    }

    public function create($username, $password, $roles = [], $active = true)
    {
        $user = (new User)
            ->setActive($active)
            ->setUsername($username)
            ->setRoles($roles)
        ;

        $user->setPassword(
            $this
                ->passwordEncoder
                ->encodePassword($user, $password)
        );

        $this->update($user);
    }
}
