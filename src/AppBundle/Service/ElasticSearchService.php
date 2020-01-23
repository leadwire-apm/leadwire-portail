<?php

namespace AppBundle\Service;

use AppBundle\Document\Application;
use AppBundle\Document\Template;
use AppBundle\Document\User;
use ATS\CoreBundle\Service\Util\AString;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;
use SensioLabs\Security\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Manager\DashboardManager;

/**
 * Class ElasticSearchService Service. Manage connexions with Kibana Rest API.
 * @package AppBundle\Service
 * @author Anis Ksontini <aksontini@ats-digital.com>
 *
 * Note:
 *  * ALL communication with Kibana is done with a JWT token in Authorization header
 *  * ALL communication with ElasticSearch is done with Basic Auth
 *
 */
class ElasticSearchService
{
    /**
     * @var mixed
     */
    private $settings;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $url;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var bool
     */
    private $hasAllUserTenant;

    /**
     * @var DashboardManager
     */
    private $DashboardManager;

    /**
     * ElasticSearchService constructor.
     * @param LoggerInterface $logger
     * @param bool $hasAllUserTenant
     * @param array $settings
     * @param DashboardManager $dashboardManager
     */
    public function __construct(
        LoggerInterface $logger,
        bool $hasAllUserTenant,
        array $settings = [],
        DashboardManager $dashboardManager
    ) {
        $this->settings = $settings;
        $this->hasAllUserTenant = $hasAllUserTenant;
        $this->logger = $logger;
        $this->dashboardManager = $dashboardManager;
        $this->httpClient = new Client(
            [
                'curl' => array(CURLOPT_SSL_VERIFYPEER => false),
                'verify' => false,
                'http_errors' => false,
            ]
        );

        $this->url = $settings['host'] . ":" . (string) $settings['port'] . "/";
    }

    /**
     * @param Application $app
     * @param User $user
     */
    public function getDashboads(Application $app, User $user, string $envName)
    {
        try {
            $dashboards = $this->filter($app, $user, $this->getRawDashboards($app, $user, $envName));
            return $dashboards;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.", 500);
        }
    }

    /**
     * @param Application $app
     * @param User $user
     */
    public function getReports(Application $app, User $user, string $envName)
    {
        try {
            $reports = $this->filter($this->getRawReports($app, $user, $envName));
            return $reports;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.", 500);
        }
    }

    /*********************************************
     *          LOWER LEVEL ACTIONS              *
     *********************************************/

    /**
     * * curl --insecure -u $es_admin_user:$es_admin_password -XGET https://es.leadwire.io/.kibana_${tenant_name}
     *
     * Returns the content of the index if it is found, FALSE otherwise
     *
     * @param string $tenantName
     *
     * @return bool|string
     */
    protected function getIndex(string $tenantName)
    {
        try {
            $response = $this->httpClient->get(
                $this->url . ".kibana_$tenantName",
                [
                    'auth' => $this->getAuth(),
                ]
            );

            $this->logger->notice(
                "leadwire.es.getIndex",
                [
                    'status_code' => $response->getStatusCode(),
                    'phrase' => $response->getReasonPhrase(),
                    'url' => $this->url . ".kibana_$tenantName",
                    'verb' => 'GET',
                ]
            );
        } catch (ClientException $e) {
            $response = $e->getResponse();
            if ($response !== null && $response->getStatusCode() === Response::HTTP_NOT_FOUND) {
                $this->logger->warning(
                    "leadwire.es.getIndex",
                    [
                        'status_code' => $response->getStatusCode(),
                        'phrase' => $response->getReasonPhrase(),
                        'url' => $this->url . ".kibana_$tenantName",
                    ]
                );

                return false;
            }
            $this->logger->error("leadwire.es.getIndex", ['error' => $e->getMessage()]);

            throw $e;
        }

        return true;
    }

    /**
     * Wrapper function
     *
     * @param string $tenantName
     *
     * @return boolean
     */
    public function indexExists(string $tenantName): bool
    {
        return true === $this->getIndex($tenantName);
    }

    /**
     * * curl --insecure -u $es_admin_user:$es_admin_password -XDELETE https://es.leadwire.io/.kibana_${tenant_name}
     *
     * Deletes an index ($tenantName) and returns true if the request succeeded. Returns false if the index was not found
     *
     * @param string $tenantName
     *
     * @return bool
     */
    public function deleteIndex(string $tenantName): bool
    {
        try {
            $response = $this->httpClient->delete(
                $this->url . ".kibana_$tenantName",
                [
                    'auth' => $this->getAuth(),
                ]
            );
            $this->logger->notice(
                "leadwire.es.deleteIndex",
                [
                    'status_code' => $response->getStatusCode(),
                    'phrase' => $response->getReasonPhrase(),
                    'url' => $this->url . ".kibana_$tenantName",
                    'verb' => 'DELETE',
                ]
            );
        } catch (ClientException $e) {
            $response = $e->getResponse();
            if ($response !== null && $response->getStatusCode() === Response::HTTP_NOT_FOUND) {
                $this->logger->warning("leadwire.es.deleteIndex", ['url' => $this->url . ".kibana_$tenantName", 'status_code' => $response->getStatusCode()]);

                return false;
            }
            $this->logger->error("leadwire.es.getIndex", ['error' => $e->getMessage()]);

            throw $e;
        }

        return true;
    }

    /*********************************************
     *          HIGHER LEVEL ACTIONS             *
     *********************************************/

    /**
     * * curl --insecure -u $es_admin_user:$es_admin_password -XGET https://es.leadwire.io/_alias/${appname}
     *
     * @param string $applicationName
     *
     * @return boolean
     */
    public function getAlias(string $applicationName): bool
    {
        $response = $this->httpClient->get($this->url . "_alias/$applicationName", ['auth' => $this->getAuth()]);

        $this->logger->notice(
            "leadwire.es.getAlias",
            [
                'status_code' => $response->getStatusCode(),
                'phrase' => $response->getReasonPhrase(),
                'url' => $this->url . "_alias/$applicationName",
                'verb' => 'GET',
            ]
        );

        if ($response->getStatusCode() === Response::HTTP_OK) {
            return true;
        } elseif ($response->getStatusCode() === Response::HTTP_NOT_FOUND) {
            return false;
        } else {
            $this->logger->error("leadwire.es.getAlias", ['reason' => $response->getReasonPhrase()]);

            throw new \Exception("Got {$response->getStatusCode()} from Guzzle", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * * curl --insecure -u $es_admin_user:$es_admin_password -H 'Content-Type: application/json' -XPOST https://es.leadwire.io/_aliases -d"{\"actions\":[{\"add\":{\"index\":\"$index_pattern_name\",\"alias\":\"$appname\"}}]}"
     *
     * @param Application $application
     *
     * @return array
     */
    public function createAlias(Application $application, string $environmentName): array
    {
        $now = $application->getCreatedAt()->format('Y-m-d');
        $createdAliases = [];
        $applicationName = $application->getName();

        $bodyString = '{"actions":[{"add":{"index":"$index_pattern_name","alias":"$appname"}}]}';
        $headers = [
            'Content-Type' => 'application/json',
        ];

        foreach ($application->getType()->getMonitoringSets() as $ms) {
            $qualifier = \strtolower($ms->getQualifier());
            $indexName = "{$qualifier}-enabled-$applicationName-$now";
            $response = $this->httpClient->delete($this->url . $indexName, ['auth' => $this->getAuth()]);
            $this->logger->notice(
                "leadwire.es.createAlias",
                [
                    'status_code' => $response->getStatusCode(),
                    'phrase' => $response->getReasonPhrase(),
                    'url' => $this->url . $indexName,
                    'verb' => 'DELETE',
                    'headers' => null,
                    'monitoring_set' => $ms->getQualifier(),
                ]
            );

            $response = $this->httpClient->put(
                $this->url . $indexName . "/_doc/1",
                [
                    'auth' => $this->getAuth(),
                    'headers' => $headers,
                    'body' => \json_encode(["@timestamp" => (new \DateTime)->format("Y-m-d\TH:i:s")]),
                ]
            );

            $this->logger->notice(
                "leadwire.es.createAlias",
                [
                    'status_code' => $response->getStatusCode(),
                    'phrase' => $response->getReasonPhrase(),
                    'url' => $this->url . $indexName . "/_doc/1",
                    'verb' => 'PUT',
                    'headers' => $headers,
                    'monitoring_set' => $ms->getQualifier(),
                ]
            );

            $body = \json_decode($bodyString, false);
            $aliasName = \strtolower($ms->getQualifier()) . "-" . $environmentName . "-$applicationName";
            $indexName = \strtolower($ms->getQualifier()) . "-*-". $environmentName ."-$applicationName-*";
            $createdAliases[] = $aliasName;
            $body->actions[0]->add->index = $indexName;
            $body->actions[0]->add->alias = $aliasName;
            $content = \json_encode($body);
            $response = $this->httpClient->post(
                $this->url . "_aliases",
                [
                    'headers' => $headers,
                    'auth' => $this->getAuth(),
                    'body' => $content,
                ]
            );

            $this->logger->notice(
                "leadwire.es.createAlias",
                [
                    'status_code' => $response->getStatusCode(),
                    'phrase' => $response->getReasonPhrase(),
                    'url' => $this->url . "_aliases",
                    'verb' => 'POST',
                    'headers' => $headers,
                ]
            );
        }

        return $createdAliases;
    }

    /**
     * @deprecated 1.1
     *
     * @param Application $application
     *
     * @return void
     */
    public function deleteApplicationIndexes(Application $application)
    {
        $this->deleteIndex("app_{$application->getUuid()}");
        $this->deleteIndex("shared_{$application->getUuid()}");
    }

    /**
     * * curl --insecure -u $es_user:$es_password -XDELETE "https://es.leadwire.io/_template/apm-$index_template_version"
     *
     * * curl --insecure -u $es_user:$es_password -XPUT "https://es.leadwire.io/_template/apm-$index_template_version" --header "Content-Type: application/json"  -d@/home/centos/pack_curl/index-template.json
     *
     * @return void
     */
    public function createIndexTemplate(Application $application, array $activeApplications)
    {
        $monitoringSets = $application->getType()->getMonitoringSets();

        foreach ($monitoringSets as $monitoringSet) {
            if ($monitoringSet->isValid() === false) {
                $this->logger->warning(
                    "leadwire.es.createIndexTemplate",
                    [
                        'event' => 'Ignoring invalid MonitoringSet',
                        'monitoring_set' => $monitoringSet->getName(),
                    ]
                );
                continue;
            }
            /** @var ?Template $template */
            $template = $monitoringSet->getTemplateByType(Template::INDEX_TEMPLATE);

            if (($template instanceof Template) === false) {
                throw new \Exception(
                    \sprintf(
                        "Template (%s) of Monitoring Set (%s) not found",
                        Template::INDEX_TEMPLATE,
                        $monitoringSet->getName()
                    )
                );
            }

            $content = $template->getContentObject();

            foreach ($activeApplications as $app) {
                // Applicable only APM templates
                if ($monitoringSet->getQualifier() === "APM") {
                    $content->aliases->{$app->getName()} = [
                        "filter" => [
                            "term" => ["context.service.name" => $app->getName()],
                        ],
                    ];
                }
            }

            $response = $this->httpClient->put(
                $this->url . "_template/{$monitoringSet->getFormattedVersion()}",
                [
                    'auth' => $this->getAuth(),
                    'headers' => [
                        "Content-Type" => "application/json",
                    ],
                    'body' => \json_encode($content),
                ]
            );

            $this->logger->notice(
                "leadwire.es.createIndexTemplate",
                [
                    'status_code' => $response->getStatusCode(),
                    'phrase' => $response->getReasonPhrase(),
                    'url' => $this->url . "_template/{$monitoringSet->getFormattedVersion()}",
                    'verb' => 'PUT',
                    'monitoring_set' => $monitoringSet->getName(),
                ]
            );
        }
    }

    /*********************************************
     *          HELPER METHODS                   *
     *********************************************/

    /**
     * @param Application $app
     * @param User $user
     */
    protected function getRawDashboards(Application $app, User $user, string $envName)
    {
        $res = [
            "Default" => [],
            "Custom" => [],
        ];

        $tenants = [
            "Default" => $this->hasAllUserTenant === true ? [$user->getAllUserIndex(), $envName . "-" . $app->getApplicationIndex()] : [$envName . "-" . $app->getApplicationIndex()],
            "Custom" => [$user->getUserIndex(), $envName . "-" . $app->getSharedIndex()],
        ];

        foreach ($tenants as $groupName => $tenantGroup) {
            foreach ($tenantGroup as $tenant) {
                $response = $this->httpClient->get(
                    $this->url . ".kibana_$tenant" . "/_search?pretty&from=0&size=10000",
                    [
                        'headers' => [
                            'Content-type' => 'application/json',
                        ],
                        'auth' => [
                            $this->settings['username'],
                            $this->settings['password'],
                        ],
                    ]
                );

                if ($response->getStatusCode() === Response::HTTP_OK) {
                    $body = \json_decode($response->getBody())->hits->hits;
                    foreach ($body as $element) {
                        if ($element->_source->type === "dashboard") {
                            $title = $element->_source->{$element->_source->type}->title;
                            $res[$groupName][] = [
                                "id" => $this->transformeId($element->_id),
                                "name" => $title,
                                "private" => $groupName === "Custom" && (new AString($tenant))->startsWith($envName . "-" ."shared_") === false,
                                "tenant" => $tenant,
                                "visible" => true,
                            ];

                        }
                    }
                } else {
                    $this->logger->error(
                        'leadwire.es.getRawDashboards',
                        [
                            'error' => $response->getReasonPhrase(),
                            'status_code' => $response->getStatusCode(),
                            'url' => $this->url . ".kibana_$tenant" . "/_search?pretty&from=0&size=10000",
                        ]
                    );
                }
            }
        }

        return $res;
    }

    /**
     * @param Application $app
     * @param User $user
     */
    protected function getRawReports(Application $app, User $user, string $envName)
    {
        $res = [
            "Default" => [],
            "Custom" => [],
        ];

        $tenants = [
            "Default" => $this->hasAllUserTenant === true ? [$user->getAllUserIndex(), $envName . "-" . $app->getApplicationIndex()] : [$envName . "-" . $app->getApplicationIndex()],
            "Custom" => [$user->getUserIndex(), $envName . "-" . $app->getSharedIndex()],
        ];

        foreach ($tenants as $groupName => $tenantGroup) {
            foreach ($tenantGroup as $tenant) {
                $response = $this->httpClient->get(
                    $this->url . ".kibana_$tenant" . "/_search?pretty&from=0&size=10000",
                    [
                        'headers' => [
                            'Content-type' => 'application/json',
                        ],
                        'auth' => [
                            $this->settings['username'],
                            $this->settings['password'],
                        ],
                    ]
                );

                if ($response->getStatusCode() === Response::HTTP_OK) {
                    $body = \json_decode($response->getBody())->hits->hits;
                    foreach ($body as $element) {
                        if ($element->_source->type === "report") {
                            $title = $element->_source->{$element->_source->type}->title;

                            $res[$groupName][] = [
                                "id" => $this->transformeId($element->_id),
                                "name" => $title,
                                "private" => $groupName === "Custom" && (new AString($tenant))->startsWith("shared_") === false,
                                "tenant" => $tenant,
                            ];
                        }
                    }
                } else {
                    $this->logger->error(
                        'leadwire.es.getRawReports',
                        [
                            'error' => $response->getReasonPhrase(),
                            'status_code' => $response->getStatusCode(),
                            'url' => $this->url . ".kibana_$tenant" . "/_search?pretty&from=0&size=10000",
                        ]
                    );
                }
            }
        }

        return $res;
    }

    protected function filter($app, $user, $dashboards)
    {
        $custom = [];
        $default = [];
        foreach ($dashboards['Custom'] as $item) {
            \preg_match_all('/\[([^]]+)\]/', $item['name'], $out);
            $theme = isset($out[1][0]) === true ? $out[1][0] : 'Misc';

            $dashboard = $this->dashboardManager->getOrCreateDashboard($user->getId(), $app->getId(), $item['id'], true);

            $custom[$theme][] = [
                "private" => $item['private'],
                "id" => $item['id'],
                "tenant" => $item['tenant'],
                "name" => \str_replace("[$theme] ", "", $item['name']),
                "visible" => $dashboard->isVisible(),
            ];
        }

        foreach ($dashboards['Default'] as $item) {
            \preg_match_all('/\[([^]]+)\]/', $item['name'], $out);
            $theme = isset($out[1][0]) === true ? $out[1][0] : 'Misc';

            $dashboard = $this->dashboardManager->getOrCreateDashboard($user->getId(), $app->getId(), $item['id'], true);


            $default[$theme][] = [
                "private" => $item['private'],
                "id" => $item['id'],
                "tenant" => $item['tenant'],
                "name" => \str_replace("[$theme] ", "", $item['name']),
                "visible" => $dashboard->isVisible(),
            ];
        }

        return [
            "Default" => $default,
            "Custom" => $custom,
        ];
    }

    protected function transformeId($id)
    {
        $id = \str_replace('dashboard:', "", $id);
        $id = \str_replace('visualization:', "", $id);

        return $id;
    }

    private function getAuth()
    {
        return [
            $this->settings['username'],
            $this->settings['password'],
        ];
    }


    public function getClusterInformations()
    {
        try {

            $response = ["nodes" => array()];

            $nodesStats = $this->httpClient->get(
                $this->url . "_nodes/stats/os,fs,jvm,indices",
    
                [
                    'headers' => [
                        'Content-type' => 'application/json',
                    ],
                    'auth' => [
                        $this->settings['username'],
                        $this->settings['password'],
                    ],
                ]
            );
    
            $clusterStats = $this->httpClient->get(
                $this->url . "_cluster/stats?human&pretty",
    
                [
                    'headers' => [
                        'Content-type' => 'application/json',
                    ],
                    'auth' => [
                        $this->settings['username'],
                        $this->settings['password'],
                    ],
                ]
            );

            $clusterHealth = $this->httpClient->get(
                $this->url . "_cluster/health",
    
                [
                    'headers' => [
                        'Content-type' => 'application/json',
                    ],
                    'auth' => [
                        $this->settings['username'],
                        $this->settings['password'],
                    ],
                ]
            );


            $clusterStats = \json_decode($clusterStats->getBody());

            $nodesStats = \json_decode($nodesStats->getBody());
            $nodesStats = (array) $nodesStats->nodes;
            $nodesStats = json_decode(json_encode($nodesStats),true);
            $clusterHealth = json_decode($clusterHealth->getBody());

            $key = '';

            $cluster = ["name" => $clusterStats->cluster_name,
            "status" => $clusterStats->status,
            "documents" => $clusterStats->indices->docs->count,
            "nodes" => $clusterHealth->number_of_nodes,
            "data_nodes" => $clusterHealth->number_of_data_nodes];

            $response["cluster"] = $cluster;
    
            foreach($nodesStats as $k => $v) {
               
               $key = $k;
               $data = array();


               $nodeOs = $this->httpClient->get(
                $this->url . "_nodes/". $key . "/info/os,roles",
    
                [
                    'headers' => [
                        'Content-type' => 'application/json',
                    ],
                    'auth' => [
                        $this->settings['username'],
                        $this->settings['password'],
                    ],
                ]
            );

            $nodeOs = \json_decode($nodeOs->getBody());
            $nodeOs = (array)$nodeOs->nodes;
            $nodeOs = json_decode(json_encode($nodeOs),true);
               
            $os = ["cpu" => $nodesStats[key]["os"]["cpu"]["percent"],
            "memory_used_byte" => $nodesStats[$key]["os"]["mem"]["used_in_bytes"],
            "memory_Total_byte" => $nodesStats[$key]["os"]["mem"]["total_in_bytes"],
            "os_name" => $nodeOs[$key]["os"]["name"],
            "os_arche" => $nodeOs[$key]["os"]["arche"],
            "os_version" => $nodeOs[$key]["os"]["version"],
            "os_allocated_processors" => $nodeOs[$key]["os"]["allocated_processors"]];

            $jvm = ["uptime_in_millis" => $nodesStats[$key]["jvm"]["uptime_in_millis"],
                    "mem_heap_used_percent" => $nodesStats[$key]["jvm"]["mem"]["heap_used_percent"],
                    "threads_count" => $nodesStats[$key]["jvm"]["threads"]["count"]];

            $fs = ["total_available_in_bytes" =>  $nodesStats[$key]["fs"]["total"]["available_in_bytes"],
                "total_in_bytes" =>  $nodesStats[$key]["fs"]["total"]["total_in_bytes"]];
            
            $data = [
                "nodeName" => $nodeOs[$key]["name"],
                "ip" =>  $nodeOs[$key]["ip"],
                "host" =>  $nodeOs[$key]["host"],
                "os" => $os,
                "jvm" => $jvm,
                "fs" => $fs,
                "documents" => $nodesStats[$key]["indices"]["docs"]["count"],
                "roles" => $nodeOs[$key]["roles"],
                "isOpen" => false,
            ];

            array_push($response["nodes"], $data);
        }
        return $response;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.", 500);
        }
    }

    public function getApplicationTransactions($app, $env)
    {
        try {

            $stats = $this->httpClient->get(
                $this->url ."*-". $env . "-". $app ."-transaction-*/_count",
    
                [
                    'headers' => [
                        'Content-type' => 'application/json',
                    ],
                    'auth' => [
                        $this->settings['username'],
                        $this->settings['password'],
                    ],
                ]
            );
    
            $res =  json_decode($stats->getBody(),true);
            return $res;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
      
    }
}
