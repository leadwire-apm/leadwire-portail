<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\ApplicationAccessLevel;
use AppBundle\Document\Application;
use AppBundle\Document\User;

class ApplicationAccessLevelService
{
    /**
     * User has read access on application
     *
     * @param User        $user
     * @param Application $application
     *
     * @return boolean
     */
    public function hasReadAccess(User $user, Application $application)
    {
        foreach ($user->getApplicationsAccessLevel() as $accessLevel) {
            if ($application->getUuid() === $accessLevel->getApplication()->getUuid && $accessLevel->getAccessLevel() === ApplicationAccessLevel::READ) {
                return true;
            }
        }

        return false;
    }

    /**
     * User has write access on application
     *
     * @param User        $user
     * @param Application $application
     *
     * @return boolean
     */
    public function hasWriteAccess(User $user, Application $application)
    {
        foreach ($user->getApplicationsAccessLevel() as $accessLevel) {
            if ($application->getUuid() === $accessLevel->getApplication()->getUuid && $accessLevel->getAccessLevel() === ApplicationAccessLevel::WRITE) {
                return true;
            }
        }

        return false;
    }
}
