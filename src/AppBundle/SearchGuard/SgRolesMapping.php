<?php

namespace AppBundle\SearchGuard;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("none")
 */
class SgRolesMapping
{
    public $sg_all_access;
    public $sg_kibana_server;
    public $sg_own_index;

    public function __construct()
    {
        $this->sg_all_access = [];
        $this->sg_all_access['users'] = ['admin'];

        $this->sg_kibana_server = [];
        $this->sg_kibana_server['users'] = ['kibanaserver'];

        $this->sg_own_index = [];
        $this->sg_own_index['users'] = ['*'];
    }

    public function addApplicationIndex($applicationName, $users)
    {
        $this->{"sg_$applicationName" . "_index"} = $users;
    }

    public function addApplicationKibanaIndex($applicationName, $users)
    {
        $this->{"sg_$applicationName" . "_kibana_index"} = $users;
    }

    public function addUserIndex($userIndex, $users)
    {
        $this->{"sg_$userIndex" . "_kibana_index"} = $users;
    }
}
