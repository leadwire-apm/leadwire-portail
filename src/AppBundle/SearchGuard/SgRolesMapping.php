<?php

namespace AppBundle\SearchGuard;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("none")
 */
class SgRolesMapping
{
    public $sg_all_access;
    public $sg_logstash;
    public $sg_kibana_server;
    public $sg_kibana_user;
    public $sg_readall;
    public $sg_manage_snapshots;
    public $sg_own_index;
    public $sg_debug_index;

    public function __construct()
    {
        $this->sg_all_access = [];
        $this->sg_all_access['readonly'] = true;
        $this->sg_all_access['backendroles'] = ['admin'];

        $this->sg_logstash = [];
        $this->sg_logstash['backendroles'] = ['logstash'];

        $this->sg_kibana_server = [];
        $this->sg_kibana_server['readonly'] = true;
        $this->sg_kibana_server['users'] = ['kibanaserver'];

        $this->sg_kibana_user = [];
        $this->sg_kibana_user['users'] = ['kibanauser'];

        $this->sg_readall = [];
        $this->sg_readall['readonly'] = true;
        $this->sg_readall['backendroles'] = ['readall'];

        $this->sg_manage_snapshots = [];
        $this->sg_manage_snapshots['readonly'] = true;
        $this->sg_manage_snapshots['backendroles'] = ['snapshotrestore'];

        $this->sg_own_index = [];
        $this->sg_own_index['users'] = ['*'];

        $this->sg_debug_index = [];
        $this->sg_debug_index['users'] = ['adm-portail', 'logstash'];
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
