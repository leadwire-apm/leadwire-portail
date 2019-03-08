<?php

namespace AppBundle\SearchGuard;

class SgRoles
{
    public $sg_all_access = [];
    public $sg_readall = [];
    public $sg_readall_and_monitor = [];
    public $sg_kibana_user = [];
    public $sg_kibana_server = [];
    public $sg_logstash = [];
    public $sg_manage_snapshots = [];
    public $sg_own_index = [];
    public $sg_debug_index = [];
    public $sg_xp_monitoring = [];
    public $sg_xp_alerting = [];
    public $sg_xp_machine_learning = [];
    public $sg_readonly_and_monitor = [];
    public $sg_monitor = [];
    public $sg_alerting = [];

    public function __construct()
    {
        $this->sg_all_access['readonly'] = true;
        $this->sg_all_access['cluster'] = ['UNLIMITED'];
        $this->sg_all_access['indices'] = ['*' => ["*" => ['UNLIMITED']]];
        $this->sg_all_access['tenants'] = ['admin_tenant' => 'RW'];

        $this->sg_readall['readonly'] = true;
        $this->sg_readall['cluster'] = ['CLUSTER_COMPOSITE_OPS_RO'];
        $this->sg_readall['indices'] = ['*' => ["*" => ['READ']]];

        $this->sg_readall_and_monitor['cluster'] = ['CLUSTER_MONITOR', 'CLUSTER_COMPOSITE_OPS_RO'];
        $this->sg_readall_and_monitor['indices'] = ['*' => ["*" => ['READ']]];

        $this->sg_kibana_user['readonly'] = true;
        $this->sg_kibana_user['cluster'] = ['CLUSTER_MONITOR', 'CLUSTER_COMPOSITE_OPS'];
        $this->sg_kibana_user['indices'] = [
            '?kibana' => [
                "*" => [
                    'MANAGE', 'INDEX', 'READ', 'DELETE',
                ],
            ],
            '*' => [
                "*" => [
                    'indices:data/read/field_caps*',
                ],
            ],
        ];

        $this->sg_kibana_server['readonly'] = true;
        $this->sg_kibana_server['cluster'] = ['CLUSTER_MONITOR', 'CLUSTER_COMPOSITE_OPS', 'cluster:admin/xpack/monitoring*', 'indices:admin/template*', 'UNLIMITED'];
        $this->sg_kibana_server['indices'] = ['*' => ["*" => ['UNLIMITED']]];

        $this->sg_logstash['cluster'] = ['CLUSTER_MONITOR', 'CLUSTER_COMPOSITE_OPS', 'indices:admin/template/get', 'indices:admin/template/put'];
        $this->sg_logstash['indices'] = ['apm-*' => ["*" => ['UNLIMITED']]];

        $this->sg_manage_snapshots['cluster'] = ['MANAGE_SNAPSHOTS'];
        $this->sg_manage_snapshots['indices'] = ['*' => ["*" => ['indices:data/write/index', 'indices:admin/create']]];

        $this->sg_own_index['cluster'] = ['CLUSTER_COMPOSITE_OPS'];
        $this->sg_own_index['indices'] = [
            '?kibana_${user_name}' => [
                "*" => ['INDICES_ALL'],
            ],
            '?kibana_all_${user_name}' => [
                "*" => ['READ'],
            ],
        ];

        $this->sg_debug_index['cluster'] = ['CLUSTER_COMPOSITE_OPS', 'UNLIMITED'];
        $this->sg_debug_index['indices'] = ['*' => ["*" => ['UNLIMITED']]];

        $this->sg_xp_monitoring['readonly'] = true;
        $this->sg_xp_monitoring['indices'] = ['?monitor*' => ["*" => ['INDICES_ALL']]];

        $this->sg_xp_alerting['readonly'] = true;
        $this->sg_xp_alerting['cluster'] = ['indices:data/read/scroll', 'cluster:admin/xpack/watcher*', 'cluster:monitor/xpack/watcher*'];
        $this->sg_xp_alerting['indices'] = [
            '?watches*' => ["*" => ['INDICES_ALL']],
            '?watcher-history-*' => ["*" => ['INDICES_ALL']],
            '?triggered_watches' => ["*" => ['INDICES_ALL']],
            '*' => ["*" => ['READ', 'indices:admin/aliases/get']],
        ];

        $this->sg_xp_machine_learning['readonly'] = true;
        $this->sg_xp_machine_learning['cluster'] = [
            'cluster:admin/persistent*',
            'cluster:internal/xpack/ml*',
            'indices:data/read/scroll*',
            'cluster:admin/xpack/ml*',
            'cluster:monitor/xpack/ml*',
        ];
        $this->sg_xp_machine_learning['indices'] = [
            '*' => ["*" => ['READ', 'indices:admin/get*']],
            '?ml-*' => ["*" => ['*']],
        ];

        $this->sg_readonly_and_monitor['cluster'] = ['CLUSTER_MONITOR', 'CLUSTER_COMPOSITE_OPS_RO'];
        $this->sg_readonly_and_monitor['indices'] = ['*' => ["*" => ['READ']]];

        $this->sg_monitor['cluster'] = [
            'cluster:admin/xpack/monitoring/*',
            'cluster:admin/ingest/pipeline/put',
            'cluster:admin/ingest/pipeline/get',
            'indices:admin/template/get',
            'indices:admin/template/put',
            'CLUSTER_MONITOR',
            'CLUSTER_COMPOSITE_OPS',
        ];
        $this->sg_monitor['indices'] = [
            '?monitor*' => ["*" => ['INDICES_ALL']],
            '?marvel*' => ["*" => ['INDICES_ALL']],
            '?kibana*' => ["*" => ['READ']],
            '*' => ["*" => ['indices:data/read/field_caps[index]', 'indices:data/read/field_caps']],
        ];

        $this->sg_alerting['cluster'] = [
            'indices:data/read/scroll',
            'cluster:admin/xpack/watcher/watch/put',
            'cluster:admin/xpack/watcher*',
            'CLUSTER_MONITOR',
            'CLUSTER_COMPOSITE_OPS',
        ];
        $this->sg_alerting['indices'] = [
            '*?kibana*' => ["*" => ['READ']],
            '?watches*' => ["*" => ['INDICES_ALL']],
            '?watcher-history-*' => ["*" => ['INDICES_ALL']],
            '?triggered_watches' => ["*" => ['INDICES_ALL']],
            '*' => ["*" => ['READ']],
        ];
    }
}
