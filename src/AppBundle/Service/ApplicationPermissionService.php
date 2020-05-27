<?php

namespace AppBundle\Service;

use AppBundle\Document\Application;
use AppBundle\Document\ApplicationPermission;
use AppBundle\Document\User;
use AppBundle\Manager\ApplicationPermissionManager;

class ApplicationPermissionService
{
    /**
     * @var ApplicationPermissionManager
     */
    private $apManager;

    public function __construct(ApplicationPermissionManager $apManager)
    {
        $this->apManager = $apManager;
    }

    public function grantPermission(Application $application, User $user, $accessType = ApplicationPermission::ACCESS_GUEST)
    {
        $listPermissions = $this->apManager->getPermissionsForUser($user);
        $alredyGranted = false;

        foreach ($listPermissions as $pirm) {
            if ($pirm->getApplication()->getId() === $application->getId()) {
                $alredyGranted = true;
            }
        }

        if(!$alredyGranted){
            $permission = new ApplicationPermission();
            $permission->setApplication($application)->setUser($user)->setAccess($accessType);
            $this->apManager->update($permission);
        } 
    }

    public function removeApplicationPermissionsByUser(Application $application, User $user){
        $this->apManager->removeApplicationPermissionsByUser($application, $user);
    }
}
