<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\EnvironementAccessLevel;
use AppBundle\Document\Environement;
use AppBundle\Document\User;

class EnvironmentAccessLevelService
{
    /**
     * User has read access on environment
     *
     * @param User        $user
     * @param Environement $environment
     *
     * @return boolean
     */
    public function hasReadAccess(User $user, Environement $environment)
    {
        foreach ($user->getEnvironementsAccessLevel() as $accessLevel) {
            if ($environment->getUuid() === $accessLevel->getEnvironement()->getUuid && $accessLevel->getAccessLevel() === EnvironementAccessLevel::READ) {
                return true;
            }
        }

        return false;
    }

    /**
     * User has write access on environment
     *
     * @param User        $user
     * @param Environement $environment
     *
     * @return boolean
     */
    public function hasWriteAccess(User $user, Environement $environment)
    {
        foreach ($user->getEnvironementsAccessLevel() as $accessLevel) {
            if ($environment->getUuid() === $accessLevel->getEnvironement()->getUuid && $accessLevel->getAccessLevel() === EnvironementAccessLevel::WRITE) {
                return true;
            }
        }

        return false;
    }
}
