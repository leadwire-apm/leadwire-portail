<?php

namespace AppBundle\SearchGuard;

class SgRoles
{
    public $sg_all_access = [];
    public $sg_kibana_server = [];
    public $sg_own_index = [];

    public function __construct()
    {
        $this->sg_all_access['cluster'] = ['UNLIMITED'];
        $this->sg_all_access['indices'] = ['*' => ["*" => ['UNLIMITED']]];
        
        $this->sg_kibana_server['cluster'] = ['CLUSTER_MONITOR', 'CLUSTER_COMPOSITE_OPS', 'cluster:admin/xpack/monitoring*', 'indices:admin/template*', 'indices:data/read/scroll*'];
        $this->sg_kibana_server['indices'] = ['?kibana*' => ['*' => ['EDIT']] , 'watcher*' => ['*' => ['indices:data/read/search', 'MANAGE', 'CREATE_INDEX', 'INDEX', 'CONSULT', 'WRITE', 'DELETE']] , '*' => ["*" => ['indices:admin/aliases*', 'indices:data/read/search']]];

        $this->sg_own_index['cluster'] = ['CLUSTER_COMPOSITE_OPS'];
        $this->sg_own_index['indices'] = [
            '?kibana_${user_name}' => [
                "*" => ['EDIT'],
            ],
            '*' => [
                "*" => ['indices:admin/aliases/get', 'indices:monitor/stats', 'indices:admin/template/get', 'indices:admin/mappings/get', 'indices:admin/get', 'indices:data/read/field_caps'],
            ],

        ];

    }
}
