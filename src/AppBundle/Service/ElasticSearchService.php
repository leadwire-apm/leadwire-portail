<?php

namespace AppBundle\Service;

use AppBundle\Document\Environment;
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
        $this->ism_min_size = $settings['ism_min_size'];
        $this->ism_min_doc_count = $settings['ism_min_doc_count'];
        $this->ism_min_index_age = $settings['ism_min_index_age'];
        $this->ism_rollover_setup = $settings['ism_rollover_setup'];
        $this->ism_delete_min_index_age = $settings['ism_delete_min_index_age'];
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

            $tenant = str_replace("-", "", $tenantName);

            $response = $this->httpClient->get(
                $this->url . ".kibana_*_" . $tenant,
                [
                    'auth' => $this->getAuth(),
                ]
            );

            $this->logger->notice(
                "leadwire.es.getIndex",
                [
                    'status_code' => $response->getStatusCode(),
                    'phrase' => $response->getReasonPhrase(),
                    'url' =>  $this->url . ".kibana_*_" . $tenant,
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
                        'url' =>  $this->url . ".kibana_*_" . $tenant,
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
            $tenant = str_replace("-", "", $tenantName);
            $response = $this->httpClient->delete(
                $this->url . ".kibana_*_" . $tenant,
                [
                    'auth' => $this->getAuth(),
                ]
            );
            $this->logger->notice(
                "leadwire.es.deleteIndex",
                [
                    'status_code' => $response->getStatusCode(),
                    'phrase' => $response->getReasonPhrase(),
                    'url' =>  $this->url . ".kibana_*_" . $tenant,
                    'verb' => 'DELETE',
                ]
            );
        } catch (ClientException $e) {
            $response = $e->getResponse();
            if ($response !== null && $response->getStatusCode() === Response::HTTP_NOT_FOUND) {
                $this->logger->warning("leadwire.es.deleteIndex", ['url' => $this->url . ".kibana_*_$tenant", 'status_code' => $response->getStatusCode()]);

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
     *
     * @param Application $application, string $environmentName, string $qualifier, string $version
     *
     * @return array
     */
    public function initIndexStateManagement(string $applicationName, string $environmentName, string $qualifier, string $version): array
    {
        $ilm_template = "000001";
        $createdAliases = [];
        $bodyString = '{"actions":[{"add":{"index":"$index_pattern_name","alias":"$appname"}}]}';
        $headers = [
            'Content-Type' => 'application/json',
        ];

            $indexName = \strtolower($qualifier) . "-" . $version . "-" . $environmentName. "-" . $applicationName . "-" . 	$ilm_template ;
			
			$aliasName = \strtolower($qualifier) . "-" . $version . "-" . $environmentName. "-" . $applicationName;		
            
	       $response = $this->httpClient->head(
                $this->url . $indexName . "/",
                [
                    'auth' => $this->getAuth(),
                    'headers' => $headers,
                ]
            );

	 $this->logger->notice(
                "leadwire.es.head",
                [
                    'status_code' => $response->getStatusCode(),
                    'phrase' => $response->getReasonPhrase(),
                    'url' => $this->url . $indexName . "/",
                    'verb' => 'HEAD',
                    'headers' => $headers,
                    'response' => $response,
                ]
            );

	// If index does not exist 
	  if ($response->getStatusCode() == '404'){


		$response = $this->httpClient->put(
                $this->url . $indexName . "/",
                [
                    'auth' => $this->getAuth(),
                    'headers' => $headers,
                ]
            );


            $this->logger->notice(
                "leadwire.es.initIndexStateManagement",
                [
                    'status_code' => $response->getStatusCode(),
                    'phrase' => $response->getReasonPhrase(),
                    'url' => $this->url . $indexName . "/",
                    'verb' => 'PUT',
                    'headers' => $headers,
                    'monitoring_set' => $qualifier,
                ]
            );

          }


            $body = \json_decode($bodyString, false);
            
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
                    'index' => $indexName
                ]
            );

        return $createdAliases;
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
            $indexName = "{$qualifier}-enabled-$environmentName-$applicationName-$now";
            /*$response = $this->httpClient->delete($this->url . $indexName, ['auth' => $this->getAuth()]);
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
	   */

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
            $aliasName = \strtolower($ms->getQualifier()) . "-" . $environmentName . "-" . $applicationName;
            $indexName = \strtolower($ms->getQualifier()) . "-*-". $environmentName . "-" . $applicationName . "-*";
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
                    'index' => $indexName
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


       public function initIndexTemplate(Application $application)
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

	    $index_template = strtolower($monitoringSet->getName()). "-" . $monitoringSet->getVersion() ;  
         
        $content = $template->getContentObject();
		
		$content->settings->{"opendistro.index_state_management.policy_id"} = "hot-warm-delete-policy";
	
      	$response = $this->httpClient->put(
                $this->url . "_template/".$index_template,
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
                    'url' => $this->url . "_template/".$index_template,
                    'verb' => 'PUT',
                    'monitoring_set' => $monitoringSet->getName(),
                ]
            );
	
        }
    }



    /**
     * * curl --insecure -u $es_user:$es_password -XDELETE "https://es.leadwire.io/_template/apm-$index_template_version"
     *
     * * curl --insecure -u $es_user:$es_password -XPUT "https://es.leadwire.io/_template/apm-$index_template_version" --header "Content-Type: application/json"  -d@/home/centos/pack_curl/index-template.json
     *
     * @return void
     */
    public function createIndexTemplate(Application $application, array $activeApplications, string $envName)
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

	    $index_template = strtolower($monitoringSet->getName()). "-" . $monitoringSet->getVersion() . "-" . $envName . "-" . $application->getName() ;
	    $index_rollover_alias = strtolower($monitoringSet->getName()). "-" . $monitoringSet->getVersion() . "-" . $envName . "-" . $application->getName() ;  
         
            $content = $template->getContentObject();

            $content->index_patterns = [ strtolower($monitoringSet->getName()). "-" . $monitoringSet->getVersion() . "-" . $envName . "-" . $application->getName() . "-*" ] ;

            $content->order = "10" ;

        if ($this->ism_rollover_setup == 'true') {
            $content->settings->{"opendistro.index_state_management.policy_id"} = "rollover-hot-warm-delete-policy";	
	    $content->settings->{"opendistro.index_state_management.rollover_alias"} = $index_rollover_alias ;
	}else {
	$content->settings->{"opendistro.index_state_management.policy_id"} = "hot-warm-delete-policy";
	} 

      	    $response = $this->httpClient->put(
                $this->url . "_template/".$index_template,
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
                    'url' => $this->url . "_template/".$index_template,
                    'verb' => 'PUT',
                    'monitoring_set' => $monitoringSet->getName(),
                ]
            );
	if ($this->ism_rollover_setup == 'true') {	
	$alias_created = $this->initIndexStateManagement( $application->getName(), $envName, $monitoringSet->getName(), $monitoringSet->getVersion()) ;
	}	
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
                
                $tenant = str_replace("-", "", $tenant);

                $response = $this->httpClient->get(
                    $this->url . ".kibana_*_" . $tenant . "/_search?pretty&from=0&size=10000",
                    [
                        'headers' => [
                            'Content-type' => 'application/json',
                            'tenant' => $tenant
                        ],
                        'auth' => [
                            $this->settings['username'],
                            $this->settings['password'],
                        ],
                    ]
                );

                $this->logger->notice(
                    'leadwire.es.getRawDashboards',
                    [
                        'status_text' => $response->getReasonPhrase(),
                        'status_code' => $response->getStatusCode(),
                        'url' =>  $this->url . ".kibana_*_" . $tenant . "/_search?pretty&from=0&size=10000",
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
                                "private" => $groupName === "Custom" && (new AString($tenant))->startsWith($envName . "shared") === false,
                                "tenant" => $tenant,
                                "visible" => true,
                            ];

                        }
                    }
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

                $tenant = str_replace("-", "", $tenant);

                $response = $this->httpClient->get(
                    $this->url . ".kibana_*_$tenant" . "/_search?pretty&from=0&size=10000",
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

                $this->logger->notice(
                    'leadwire.es.getRawReports',
                    [
                        'error' => $response->getReasonPhrase(),
                        'status_code' => $response->getStatusCode(),
                        $this->url . ".kibana_*_$tenant" . "/_search?pretty&from=0&size=10000",
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

            $dashboard = $this->dashboardManager->getOrCreateDashboard($app->getId(), $item['id'], true);

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

            $dashboard = $this->dashboardManager->getOrCreateDashboard($app->getId(), $item['id'], true);


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
               
            $os = ["cpu" => $nodesStats[$key]["os"]["cpu"]["percent"],
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

                $this->url ."*-". $env ."-". $app ."-*/_count",
    
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

            $this->logger->notice(
                'leadwire.es.getApplicationTransactions',
                [
                    'status_text' => $stats->getReasonPhrase(),
                    'status_code' => $stats->getStatusCode(),
                    'url' => $this->url ."*-". $env ."-". $app ."-*/_count",
                ]
            );
    
            $res =  json_decode($stats->getBody(),true);
            return $res;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
      
    }
    
    function getReports($appName, $envName){
        try {
            
            $body = [
                "query" => [
                    "bool" => [
                        "must" => [
                            array( [ "match" => ["watcher" => $envName . "-" . $appName . "-*"]] )
                        ]
                    ]
                ],
		"sort" => [
                   "@timestamp" => [
                         "order" => "asc"
                  ]
                ]

            ];

            $response = $this->httpClient->get(

                $this->url . "watcher_alarms-*/_search?size=10000",
                [
                    'auth' => $this->getAuth(),
                    'headers' => [
                        "Content-Type" => "application/json",
                    ],
                    'body' => \json_encode($body),
                ]

            );

            $this->logger->notice(
                "leadwire.es.getReports",
                [
                    'url' => $this->url . "watcher_alarms-*/_search?size=10000",
                    'verb' => 'GET',
                    'status_code' => $response->getStatusCode(),
                    'status_text' => $response->getReasonPhrase()
                ]
            );

            $res =  \json_decode($response->getBody(),true);
            return $res['hits']['hits'];

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
    }
    
    /*****************************opendistro*************************************/


    function createUser(User $user): bool{
        try {
            $status = false;
            $response = $this->httpClient->put(

                $this->url . "_opendistro/_security/api/internalusers/" . $user->getUsername(),
                [
                    'auth' => $this->getAuth(),
                    'headers' => [
                        "Content-Type" => "application/json",
                    ],
                    'body' => \json_encode(["password" => $user->getUsername()]),
                ]

            );

            $this->logger->notice(
                "leadwire.opendistro.createUser",
                [
                    'url' => $this->url . "_opendistro/_security/api/internalusers/" . $user->getUsername(),
                    'verb' => 'PUT',
                    'status_code' => $response->getStatusCode(),
                    'status_text' => $response->getReasonPhrase()

                ]
            );
            
            if($response->getStatusCode() == 201){
                $status= true;
            }

            return $status;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
    }

    function createTenant(string $tenantName): bool{
        try {

            $status = false;
            $response = $this->httpClient->put(

                $this->url . "_opendistro/_security/api/tenants/" . $tenantName,
                [
                    'auth' => $this->getAuth(),
                    'headers' => [
                        "Content-Type" => "application/json"
                    ],
                    'body' => \json_encode(["description" => ""]),
                ]

            );

            $this->logger->notice(
                "leadwire.opendistro.createTenant",
                [
                    'url' => $this->url . "_opendistro/_security/api/tenants/" . $tenantName,
                    'verb' => 'PUT',
                    'status_code' => $response->getStatusCode(),
                    'status_text' => $response->getReasonPhrase()

                ]
            );
            
            if($response->getStatusCode() == 201){
                $status= true;
            }

            return $status;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
    }


 function deleteTemplatesForApplication(Application $application): bool{
        try {

            $status = false;
            $response = $this->httpClient->delete(

                $this->url . "_template/*-" . $application->getName() ,
                [
                    'auth' => $this->getAuth(),
                    'headers' => [
                        "Content-Type" => "application/json"
                    ],
                ]

            );

            $this->logger->notice(
                "leadwire.opendistro.deleteTemplates",
                [
                    'url' => $this->url . "_template/*-" . $application->getName() ,
                    'verb' => 'DELETE',
                    'status_code' => $response->getStatusCode(),
                    'status_text' => $response->getReasonPhrase()
                ]
            );

            if($response->getStatusCode() == 200){
                $status= true;
            }
	  return $status;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
    }


    function deleteTemplatesForEnvironment(Environment $environment): bool{
        try {

            $status = false;
            $response = $this->httpClient->delete(

                $this->url . "_template/*-" . $environment->getName() . "-*" ,
                [
                    'auth' => $this->getAuth(),
                    'headers' => [
                        "Content-Type" => "application/json"
                    ],
                ]

            );

            $this->logger->notice(
                "leadwire.opendistro.deleteTemplates",
                [
                    'url' => $this->url . "_template/*-" . $environment->getName() . "-*" ,
                    'verb' => 'DELETE',
                    'status_code' => $response->getStatusCode(),
                    'status_text' => $response->getReasonPhrase()
                ]
            );

            if($response->getStatusCode() == 200){
                $status= true;
            }
          return $status;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
    }



    function deleteTenant(string $tenantName): bool{
        try {

            $status = false;
            $response = $this->httpClient->delete(

                $this->url . "_opendistro/_security/api/tenants/" . $tenantName,
                [
                    'auth' => $this->getAuth(),
                    'headers' => [
                        "Content-Type" => "application/json"
                    ],
                ]

            );

            $this->logger->notice(
                "leadwire.opendistro.deleteTenant",
                [
                    'url' => $this->url . "_opendistro/_security/api/tenants/" . $tenantName,
                    'verb' => 'DELETE',
                    'status_code' => $response->getStatusCode(),
                    'status_text' => $response->getReasonPhrase()
                ]
            );
            
            if($response->getStatusCode() == 200){
                $status= true;
            }

            return $status;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
    }


function createLeadwireRolesMapping(): bool{

 $status = false;
try {

$rolesmapping = array (
  'users' => 
  array (
    0 => '*',
  ),
);


     $response = $this->httpClient->put(

                $this->url . "_opendistro/_security/api/rolesmapping/leadwire" ,
                [
                    'auth' => $this->getAuth(),
                    'headers' => [
                        "Content-Type" => "application/json",
                    ],
                    'body' => \json_encode($rolesmapping),
                ]

            );

            $this->logger->notice(
                "leadwire.opendistro.createLeadwireRolesMapping",
                [
                    'url' => $this->url . "_opendistro/_security/api/rolesmapping/leadwire" ,
                    'verb' => 'PUT',
                    'status_code' => $response->getStatusCode(),
                    'status_text' => $response->getReasonPhrase()
                ]
            );

            if($response->getStatusCode() == 201){
                $status= true;
            }

            return $status;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
    }




    function createLeadwireRole(): bool{
 $status = false;
try {

$role = array (
    "index_permissions" => 
    array (
      0 => 
      array (
        "index_patterns" => 
        array (
          0 => "*",
        ),
        "allowed_actions" => 
        array (
          0 => "indices:data/read/search*",
          1 => "indices:data/read/field_caps*",
	  2 => "indices:data/read/mget*",
        ),
      ),
    ),
);

$response = $this->httpClient->put(

                $this->url . "_opendistro/_security/api/roles/leadwire" ,
                [
                    'auth' => $this->getAuth(),
                    'headers' => [
                        "Content-Type" => "application/json",
                    ],
                    'body' => \json_encode($role),
                ]

            );

            $this->logger->notice(
                "leadwire.opendistro.createLeadwireRole",
                [
                    'url' => $this->url . "_opendistro/_security/api/roles/leadwire" ,
                    'verb' => 'PUT',
                    'status_code' => $response->getStatusCode(),
                    'status_text' => $response->getReasonPhrase()
                ]
            );

            if($response->getStatusCode() == 201){
                $status= true;
            }

            return $status;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
    }


    function createConfig(): bool{
               $status = false;
try {

$config =  [
  "dynamic" => 
  array (
    "filtered_alias_mode" => "warn",
    "disable_rest_auth" => false,
    "disable_intertransport_auth" => false,
    "respect_request_indices_options" => false,
    "kibana" => 
    array (
      "multitenancy_enabled" => true,
      "server_username" => "kibanaserver",
      "index" => ".kibana",
    ),
    "http" => 
    array (
      "anonymous_auth_enabled" => false,
      "xff" => 
      array (
        "enabled" => true,
        "internalProxies" => ".*",
        "remoteIpHeader" => "x-forwarded-for",
      ),
    ),
    "authc" => 
    array (
      "basic_internal_auth_domain" => 
      array (
        "http_enabled" => true,
        "transport_enabled" => true,
        "order" => 2,
        "http_authenticator" => 
        array (
          "challenge" => true,
          "type" => "basic",
        ),
        "authentication_backend" => 
        array (
          "type" => "intern",
        ),
        "description" => "Authenticate via HTTP Basic against internal users database",
      ),
      "proxy_auth_domain" => 
      array (
        "http_enabled" => true,
        "transport_enabled" => true,
        "order" => 1,
        "http_authenticator" => 
        array (
          "challenge" => false,
          "type" => "proxy",
          "config" => 
          array (
            "user_header" => "x-proxy-user",
            "roles_header" => "x-proxy-roles",
          ),
        ),
        "authentication_backend" => 
        array (
          "type" => "noop",
        ),
        "description" => "Authenticate via proxy",
      ),
      "clientcert_auth_domain" => 
      array (
        "http_enabled" => true,
        "transport_enabled" => true,
        "order" => 3,
        "http_authenticator" => 
        array (
          "challenge" => false,
          "type" => "clientcert",
          "config" => 
          array (
            "username_attribute" => "cn",
          ),
        ),
        "authentication_backend" => 
        array (
          "type" => "noop",
        ),
        "description" => "Authenticate via SSL client certificates",
      ),
    ),
    "do_not_fail_on_forbidden" => false,
    "multi_rolespan_enabled" => true,
    "hosts_resolver_mode" => "ip-only",
    "do_not_fail_on_forbidden_empty" => false,
  ),
] ;

 $response = $this->httpClient->put(

                $this->url . "_opendistro/_security/api/securityconfig/config" ,
                [
                    'auth' => $this->getAuth(),
                    'headers' => [
                        "Content-Type" => "application/json",
                    ],
                    'body' => \json_encode($config),
                ]

            );

            $this->logger->notice(
                "leadwire.opendistro.createConfig",
                [
                    'url' => $this->url . "_opendistro/_security/api/securityconfig/config" ,
                    'verb' => 'PUT',
                    'status_code' => $response->getStatusCode(),
                    'status_text' => $response->getReasonPhrase()
                ]
            );

            if($response->getStatusCode() == 201){
                $status= true;
            }

            return $status;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
    }

    

    function createRole(string $envName, 
                        string $applicationName, 
                        array $index_patterns, 
                        array $tenant_patterns,  
                        array $allowed_actions,
                        bool $isWrite,
                        bool $isWatcher): bool{
        try {

            $status = false;

            $role = [
                "cluster_permissions" => array("cluster_composite_ops", "indices_monitor"),
                "index_permissions" => array(["index_patterns" => $index_patterns,
                "dls" => "",
                "fls" => array(),
                "masked_fields" => array(),
                "allowed_actions" => array("kibana_all_read")]),
                "tenant_permissions" => array([
                    "tenant_patterns" => $tenant_patterns,
                    "allowed_actions" => $allowed_actions,
                ])
            ];

            if($isWatcher) {
                $url = $this->url . "_opendistro/_security/api/roles/role_" .  $envName . "_watcher_" . $applicationName;
            } else {
                $url = $this->url . "_opendistro/_security/api/roles/role_" .  $envName . "_" . $applicationName;
            }

            if($isWrite){
                $url = $url . "_write";
            } else {
                $url = $url . "_read";
            }



            $response = $this->httpClient->put(

                $url,
                [
                    'auth' => $this->getAuth(),
                    'headers' => [
                        "Content-Type" => "application/json",
                    ],
                    'body' => \json_encode($role),
                ]

            );

            $this->logger->notice(
                "leadwire.opendistro.createRole",
                [
                    'url' => $url,
                    'verb' => 'PUT',
                    'status_code' => $response->getStatusCode(),
                    'status_text' => $response->getReasonPhrase()
                ]
            );
            
            if($response->getStatusCode() == 201){
                $status= true;
            }

            return $status;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
    }

    function patchRole(string $envName, string $applicationName, string $path, string $action, array $value): bool{
        try {

            $status = false;
            
            $body = array(
                ["op" => $action,
                "path" => $path,
                "value" => $value]
            );

            $response = $this->httpClient->patch(

                $this->url . "_opendistro/_security/api/roles/role_" . $envName . "_" . $applicationName,
                [
                    'auth' => $this->getAuth(),
                    'headers' => [
                        "Content-Type" => "application/json",
                    ],
                    'body' => \json_encode($body),
                ]

            );

            $this->logger->notice(
                "leadwire.opendistro.patchRole",
                [
                    'url' => url . "_opendistro/_security/api/roles/role_" . $envName . "_" . $applicationName,
                    'verb' => 'PATCH',
                    'status_code' => $response->getStatusCode(),
                    'status_text' => $response->getReasonPhrase()
                ]
            );
            
            if($response->getStatusCode() == 200){
                $status= true;
            }

            return $status;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
    }

    function deleteRole(string $envName, string $applicationName, bool $isWrite, bool $isWatcher): bool{
        try {

            $status = false;

            if($isWatcher){
                $url = $this->url . "_opendistro/_security/api/roles/role_" . $envName . "_watcher_" . $applicationName;
            } else {
                $url = $this->url . "_opendistro/_security/api/roles/role_" . $envName . "_" . $applicationName;
            }

            if($isWrite){
                $url = $url . "_write";
            } else {
                $url = $url . "_read";
            }

            $response = $this->httpClient->delete(

                $url,
                [
                    'auth' => $this->getAuth(),
                    'headers' => [
                        "Content-Type" => "application/json",
                    ]
                ]

            );

            $this->logger->notice(
                "leadwire.opendistro.deleteRole",
                [
                    'url' => $url,
                    'verb' => 'PATCH',
                    'status_code' => $response->getStatusCode(),
                    'status_text' => $response->getReasonPhrase()
                ]
            );
            
            if($response->getStatusCode() == 200){
                $status= true;
            }

            return $status;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
    }
    

    function createRoleMapping(string $envName,
                               string $applicationName,
                               string $userName='',
                               bool $isWrite,
                               bool $isWatcher): bool{
        try {

            $status = false;

            $role = [
                "users" => array($userName)
            ];

            if($isWatcher) {
                $url = $this->url . "_opendistro/_security/api/rolesmapping/role_" . $envName . "_watcher_" . $applicationName;
            } else {
                $url = $this->url . "_opendistro/_security/api/rolesmapping/role_" . $envName . "_" . $applicationName;
            }

            if($isWrite){
                $url = $url . "_write";
            }else{
                $url = $url . "_read";
            }

            $response = $this->httpClient->put(
                $url,
                [
                    'auth' => $this->getAuth(),
                    'headers' => [
                        "Content-Type" => "application/json",
                    ],
                    'body' => \json_encode($role),
                ]

            );

            $this->logger->notice(
                "leadwire.opendistro.createRoleMapping",
                [
                    'url' => $url,
                    'verb' => 'PUT',
                    'status_code' => $response->getStatusCode(),
                    'status_text' => $response->getReasonPhrase()
                ]
            );
            
            if($response->getStatusCode() == 201){
                $status= true;
            }

            return $status;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
    }

    private function patchRoleMapping(string $action, string $envName, string $applicationName, array $users, bool $isWrite, bool $isWatcher): bool{
        try {

            $status = false;
            $path = "/role_" . $envName . "_" . $applicationName;

            if($isWatcher) {
                $path = "/role_" . $envName . "_watcher_" . $applicationName;
            }


            if($isWrite === true) {
                $path = $path . "_write";
            } else {
                $path = $path . "_read";
            }

            $body = array(
                ["op" => $action,
                "path" => $path,
                "value" => [
                    "users" => $users,
                ]]
            );

            $response = $this->httpClient->patch(

                $this->url . "_opendistro/_security/api/rolesmapping",
                [
                    'auth' => $this->getAuth(),
                    'headers' => [
                        "Content-Type" => "application/json",
                    ],
                    'body' => \json_encode($body),
                ]

            );

            $this->logger->notice(
                "leadwire.opendistro.patchRoleMapping",
                [
                    'url' => $this->url . "_opendistro/_security/api/rolesmapping",
                    'verb' => 'PATCH',
                    'status_code' => $response->getStatusCode(),
                    'status_text' => $response->getReasonPhrase()
                ]
            );
            
            if($response->getStatusCode() == 200){
                $status= true;
            }

            return $status;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
    }

    function deleteRoleMapping(string $envName, string $applicationName, bool $isWrite, bool $isWatcher): bool{
        try {

            $status = false;

            if($isWatcher){
                $url = $this->url . "_opendistro/_security/api/rolesmapping/role_" . $envName . "_watcher_" .$applicationName; 
            } else {
                $url = $this->url . "_opendistro/_security/api/rolesmapping/role_" . $envName . "_" .$applicationName; 
            }

            if($isWrite === true){
                $url = $url . "_write";
            } else {
                $url = $url . "_read";
            }

            $response = $this->httpClient->delete(

                $url,
                [
                    'auth' => $this->getAuth(),
                    'headers' => [
                        "Content-Type" => "application/json",
                    ],
                ]

            );

            $this->logger->notice(
                "leadwire.opendistro.createRoleMapping",
                [
                    'url' => $url,
                    'verb' => 'DELETE',
                    'status_code' => $response->getStatusCode(),
                    'status_text' => $response->getReasonPhrase()
                ]
            );
            
            if($response->getStatusCode() == 201){
                $status= true;
            }

            return $status;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
    }

    private function getRoleMapping(String $envName, string $applicationName, bool $isWrite, bool $isWatcher): array{
        try {

            $status = false;

            if($isWatcher) {
                $url = $this->url . "_opendistro/_security/api/rolesmapping/role_" . $envName . "_watcher_" . $applicationName;
            } else {
                $url = $this->url . "_opendistro/_security/api/rolesmapping/role_" . $envName . "_" . $applicationName;
            }

            if($isWrite === true){
                $url = $url . "_write";
            } else {
                $url = $url . "_read";            
            }

            $response = $this->httpClient->get(

                $url,
                [
                    'auth' => $this->getAuth(),
                    'headers' => [
                        "Content-Type" => "application/json",
                    ],
                ]

            );

            $this->logger->notice(
                "leadwire.opendistro.getRoleMapping",
                [
                    'url' => $url,
                    'verb' => 'GET',
                    'status_code' => $response->getStatusCode(),
                    'status_text' => $response->getReasonPhrase()
                ]
            );
            if($response->getStatusCode() == 200){
               
                $role = "role_" . $envName . "_" . $applicationName;
                
                if($isWatcher){
                    $role = "role_" . $envName . "_watcher_" . $applicationName;
                }

                if($isWrite){
                    $role = $role . "_write";
                } else {
                    $role = $role . "_read";
                }

                $res = \json_decode($response->getBody());
                $res = (array)$res->$role;
                return $res["users"];
            } else {
                return array();
            }

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
    }

    public function updateRoleMapping(string $action,
                                      string $envName, 
                                      User $user, 
                                      string $applicationName,
                                      bool $isWrite,
                                      bool $isWatcher){

        $users = $this->getRoleMapping($envName, $applicationName, $isWrite, $isWatcher);

        switch ($action) {
            case "add":
                if (!in_array($user->getName(), $users)) {
                    array_push($users, $user->getName());
                }
                break;
            case "delete":
               if (in_array($user->getName(), $users)) {
                    $key = array_search($user->getName(), $users);
                    unset($users[$key]);
                }
                break;
            }
        return $this->patchRoleMapping("replace", $envName, $applicationName, $users, $isWrite, $isWatcher);
    }

    function createAlert(string $appName, string $envName, string $type ): bool{
        try {

            $status = false;

            $role = [
                "name" => $envName . "-" . $appName . "-webhook",
                "type" => $type,
                "custom_webhook" => [
                    "scheme" => "HTTPS",
                    "url" => "",
                    "host" => "apm.leadwire.io",
                    "port" => 443,
                    "path" => "/api/webhooks/". $envName . "/" . $appName . "/",
                    "header_params" => ["Content-Type" => "application/json"],
                    "query_params" => ["token" => "generer_un_jwt_token"]
                ]
            ];
            
            $url = $this->url . "_opendistro/_alerting/destinations";
           
            $response = $this->httpClient->post(

                $url,
                [
                    'auth' => $this->getAuth(),
                    'headers' => [
                        "Content-Type" => "application/json",
                    ],
                    'body' => \json_encode($role),
                ]

            );

            $this->logger->notice(
                "leadwire.opendistro.createDestination",
                [
                    'url' => $url,
                    'verb' => 'POST',
                    'status_code' => $response->getStatusCode(),
                    'status_text' => $response->getReasonPhrase()
                ]
            );
            
            if($response->getStatusCode() == 201){
                $status= true;
            }

            return $status;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
    }

    function createPolicy(){
        try {

            $status = false;

            $policy = [
                "policy"=>[
                    "description"=>"hot warm delete workflow",
                    "default_state"=>"hot",
                    "schema_version"=>1,
                    "states"=>array(
                        [
                            "name"=>"hot",
                            "actions"=>array(["replica_count"=>["number_of_replicas"=>1]]),
                            "transitions"=>array([
                                "state_name"=>"warm",
                                "conditions"=>["min_index_age"=>"1d"]]
                            )
                        ],
                        [
                            "name"=>"warm",
                            "actions"=>array(["replica_count"=>["number_of_replicas"=>3]]),
                            "transitions"=>array([
                                "state_name"=>"delete",
                                "conditions"=>["min_index_age"=>$this->ism_delete_min_index_age]]
                            )
                        ],
                        [
                            "name"=>"delete",
                            "actions"=>array(
                                ["notification"=> [
                                    "destination"=>[
                                        "chime"=>[
                                            "url"=>"<URL>"
                                        ]
                                    ],
                                    "message_template"=>[
                                        "source"=>"The index is being deleted"
                                        ]
                                    ]
                                ]
                            )
                        ]
                    )
                ]
            ];
            
            $url = $this->url . "_opendistro/_ism/policies/hot-warm-delete-policy";
           
            $response = $this->httpClient->put(

                $url,
                [
                    'auth' => $this->getAuth(),
                    'headers' => [
                        "Content-Type" => "application/json",
                    ],
                    'body' => \json_encode($policy),
                ]

            );

            $this->logger->notice(
                "leadwire.opendistro.createPolicy",
                [
                    'url' => $url,
                    'verb' => 'PUT',
                    'status_code' => $response->getStatusCode(),
                    'status_text' => $response->getReasonPhrase()
                ]
            );
            
            if($response->getStatusCode() == 201){
                $status= true;
            }

            return $status;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
    }


     function createRolloverPolicy(){
        try {

            $status = false;

            $policy = [
                "policy"=>[
                    "description"=>"rollover hot warm delete workflow",
                    "default_state"=>"hot",
                    "schema_version"=>1,
                    "states"=>array(
                        [
                            "name"=>"hot",
			    "actions"=>array(["rollover"=>["min_doc_count"=>$this->ism_min_doc_count,
                                                           "min_index_age"=>$this->ism_min_index_age,
                                                           "min_size"=>$this->ism_min_size
                                                ]],
					     ["replica_count"=>["number_of_replicas"=>1]]),
                            "transitions"=>array(["state_name"=>"warm"])
                        ],
                        [
                            "name"=>"warm",
                            "actions"=>array(["replica_count"=>["number_of_replicas"=>3]]),
                            "transitions"=>array([
                                "state_name"=>"delete",
                                "conditions"=>["min_index_age"=>$this->ism_delete_min_index_age]]
                            )
                        ],
                        [
                            "name"=>"delete",
                            "actions"=>array(
                                ["notification"=> [
                                    "destination"=>[
                                        "chime"=>[
                                            "url"=>"<URL>"
                                        ]
                                    ],
                                    "message_template"=>[
                                        "source"=>"The index is being deleted"
                                        ]
                                    ]
                                ]
                            )
                        ]
                    )
                ]
            ];
            
            $url = $this->url . "_opendistro/_ism/policies/rollover-hot-warm-delete-policy";
           
            $response = $this->httpClient->put(

                $url,
                [
                    'auth' => $this->getAuth(),
                    'headers' => [
                        "Content-Type" => "application/json",
                    ],
                    'body' => \json_encode($policy),
                ]

            );

            $this->logger->notice(
                "leadwire.opendistro.createRolloverPolicy",
                [
                    'url' => $url,
                    'verb' => 'PUT',
                    'status_code' => $response->getStatusCode(),
                    'status_text' => $response->getReasonPhrase()
                ]
            );
            
            if($response->getStatusCode() == 201){
                $status= true;
            }

            return $status;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
    }

    function deletePolicy(string $policyName){
        try {

            $status= false;

            $url = $this->url . "_opendistro/_ism/policies/" . $policyName;
           
            $response = $this->httpClient->delete(

                $url,
                [
                    'auth' => $this->getAuth(),
                ]

            );

            $this->logger->notice(
                "leadwire.opendistro.deletePolicy",
                [
                    'url' => $url,
                    'verb' => 'DELETE',
                    'status_code' => $response->getStatusCode(),
                    'status_text' => $response->getReasonPhrase()
                ]
            );
            
            if($response->getStatusCode() == 200){
                $status= true;
            }

            return $status;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
    }
    
    /****************************************************************************/
    
    
    
    ///Purges
    
    
        function purgeIndices(): bool{
        try {

            $status = false;
            $response = $this->httpClient->delete(

                $this->url . "apm-*,metricbeat-*,.kibana*" ,
                [
                    'auth' => $this->getAuth(),
                    'headers' => [
                        "Content-Type" => "application/json"
                    ],
                ]

            );

            $this->logger->notice(
                "leadwire.es.purge.indices",
                [
                    'url' => $this->url . "apm-*,metricbeat-*,.kibana*" ,
                    'verb' => 'DELETE',
                    'status_code' => $response->getStatusCode(),
                    'status_text' => $response->getReasonPhrase()
                ]
            );
            
            if($response->getStatusCode() == 200){
                $status= true;
            }

            return $status;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
    }
    
     function purgeAliases(): bool{
        try {

            $status = false;
            $response = $this->httpClient->delete(

                $this->url . "*test*/_aliases/*" ,
                [
                    'auth' => $this->getAuth(),
                    'headers' => [
                        "Content-Type" => "application/json"
                    ],
                ]

            );

            $this->logger->notice(
                "leadwire.es.purge.aliases",
                [
                    'url' => $this->url . "*/_aliases/*" ,
                    'verb' => 'DELETE',
                    'status_code' => $response->getStatusCode(),
                    'status_text' => $response->getReasonPhrase()
                ]
            );
            
            if($response->getStatusCode() == 200){
                $status= true;
            }

            return $status;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
    }
    
    
     function purgeTemplates(): bool{
        try {

            $status = false;
            $response = $this->httpClient->delete(

                $this->url . "_template/*7.2.1*" ,
                [
                    'auth' => $this->getAuth(),
                    'headers' => [
                        "Content-Type" => "application/json"
                    ],
                ]

            );

            $this->logger->notice(
                "leadwire.es.purge.templates",
                [
                    'url' => $this->url . "_template/*" ,
                    'verb' => 'DELETE',
                    'status_code' => $response->getStatusCode(),
                    'status_text' => $response->getReasonPhrase()
                ]
            );
            
            if($response->getStatusCode() == 200){
                $status= true;
            }

            return $status;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
    }
    
    function addPipline($name, $body): bool{
        try {

            $status = false;

            $url = $this->url . "_ingest/pipeline/" . $name ;
           
            $response = $this->httpClient->put(

                $url,
                [
                    'auth' => $this->getAuth(),
                    'headers' => [
                        "Content-Type" => "application/json",
                    ],
                    'body' => \json_encode($body),
                ]

            );

            $this->logger->notice(
                "leadwire.opendistro.addPipline",
                [
                    'url' => $url,
                    'verb' => 'PUT',
                    'status_code' => $response->getStatusCode(),
                    'status_text' => $response->getReasonPhrase()
                ]
            );
            
            if($response->getStatusCode() == 201){
                $status= true;
            }

            return $status;

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.",400);
        }
    }
    
    
}
