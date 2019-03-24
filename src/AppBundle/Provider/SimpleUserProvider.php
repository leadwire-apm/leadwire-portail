<?php declare (strict_types = 1);

namespace AppBundle\Provider;

use AppBundle\Document\User;
use AppBundle\Manager\UserManager;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Simple user provider
 *
 * @author Wajih WERIEMI <wweriemi@ats-digital.com>
 */
class SimpleUserProvider implements UserProviderInterface
{
    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * Constructor
     *
     * @param UserManager $userManager
     */
    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        $user = $this->userManager->getUserByUsername($username);
        if ($user !== null) {
            return $user;
        }

        throw new UsernameNotFoundException(
            sprintf('User with username "%s" does not exist.', $username)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if ($this->supportsClass($class) === false) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', $class)
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return User::class === $class;
    }
}
