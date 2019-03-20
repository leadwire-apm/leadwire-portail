<?php

namespace AppBundle\Service;

use AppBundle\Document\Application;
use AppBundle\Document\User;
use AppBundle\Manager\TemplateManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;
use SensioLabs\Security\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

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
     * @var TemplateManager
     */
    private $templateManager;

    /**
     * ElasticSearchService constructor.
     * @param LoggerInterface $logger
     * @param TemplateManager $templateManager
     * @param array $settings
     */
    public function __construct(
        LoggerInterface $logger,
        TemplateManager $templateManager,
        array $settings = []
    ) {
        $this->settings = $settings;
        $this->templateManager = $templateManager;
        $this->logger = $logger;
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
     */
    public function getDashboads(Application $app)
    {
        try {
            return $this->filter($this->getRawDashboards($app));
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
                    'url' => $this->url . ".kibana_$tenantName",
                    'verb' => 'GET',
                    'status_code' => $response->getStatusCode(),
                ]
            );
        } catch (ClientException $e) {
            $response = $e->getResponse();
            if ($response !== null && $response->getStatusCode() === Response::HTTP_NOT_FOUND) {
                $this->logger->warning("leadwire.es.getIndex", ['url' => $this->url . ".kibana_$tenantName", 'status_code' => $response->getStatusCode()]);

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
                    'url' => $this->url . ".kibana_$tenantName",
                    'verb' => 'DELETE',
                    'status_code' => $response->getStatusCode(),
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
                'url' => $this->url . "_alias/$applicationName",
                'verb' => 'GET',
                'status_code' => $response->getStatusCode(),
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
     * @param string $applicationName
     *
     * @return bool
     */
    public function createAlias(string $applicationName): bool
    {
        $now = (new \DateTime())->format('Y-m-d');

        $response = $this->httpClient->delete($this->url . "enabled-$applicationName-$now");

        $this->logger->notice(
            "leadwire.es.createAlias",
            [
                'url' => $this->url . "enabled-{$applicationName}-{$now}",
                'verb' => 'PUT',
                'status_code' => $response->getStatusCode(),
            ]
        );
        $response = $this->httpClient->put($this->url . "enabled-$applicationName-$now");

        $this->logger->notice(
            "leadwire.es.createAlias",
            [
                'url' => $this->url . "enabled-{$applicationName}-{$now}",
                'verb' => 'PUT',
                'status_code' => $response->getStatusCode(),
            ]
        );

        $bodyString = '{"actions":[{"add":{"index":"$index_pattern_name","alias":"$appname"}}]}';
        $body = json_decode($bodyString, false);
        $body->actions[0]->add->index = "*-$applicationName-*";
        $body->actions[0]->add->alias = $applicationName;

        $content = json_encode($body);

        $headers = [
            'Content-Type' => 'application/json',
        ];

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
                'url' => $this->url . "_aliases",
                'verb' => 'POST',
                'body' => $content,
                'headers' => $headers,
                'status_code' => $response->getStatusCode(),
            ]
        );

        return $response->getStatusCode() === Response::HTTP_OK;
    }

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
        $template = $this->templateManager->getOneBy(
            [
                'applicationType.id' => $application->getType()->getId(),
                'name' => 'index-template',
            ]
        );

        if ($template === null) {
            throw new \Exception("Template (index-template) not found");
        }

        $content = $template->getContentObject();

        foreach ($activeApplications as $app) {
            $content->aliases->{$app->getName()} = [
                "filter" => [
                    "term" => ["context.service.name" => $app->getName()],
                ],
            ];
        }

        $response = $this->httpClient->delete($this->url . "_template/apm-6.5.1", ['auth' => $this->getAuth()]);

        $this->logger->notice(
            "leadwire.es.createIndexTemplate",
            [
                'url' => $this->url . "_template/apm-6.5.1",
                'verb' => 'DELETE',
                'status_code' => $response->getStatusCode(),
            ]
        );

        $response = $this->httpClient->put(
            $this->url . "_template/apm-6.5.1",
            [
                'auth' => $this->getAuth(),
                'headers' => [
                    "Content-Type" => "application/json",
                ],
                'body' => json_encode($content),
            ]
        );

        $this->logger->notice(
            "leadwire.es.createIndexTemplate",
            [
                'url' => $this->url . "_template/apm-6.5.1",
                'verb' => 'PUT',
                'body' => json_encode($content),
                'status_code' => $response->getStatusCode(),
            ]
        );
    }

    /*********************************************
     *          HELPER METHODS                   *
     *********************************************/

    /**
     * @param Application $app
     */
    protected function getRawDashboards(Application $app)
    {
        $res = [];

        $tenants = ["all_user_{$app->getOwner()->getUuid()}", "app_{$app->getUuid()}"];

        foreach ($tenants as $index => $tenant) {
            try {
                $key = $index === 0 ? "Default" : "Custom";
                $res[$key] = isset($res[$key]) === true ? $res[$key] : [];
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
                    $body = json_decode($response->getBody())->hits->hits;
                    foreach ($body as $element) {
                        if (in_array($element->_source->type, array("dashboard" /*, "visualization"*/)) === true) {
                            $title = $element->_source->{$element->_source->type}->title;

                            $res[$key][] = [
                                "id" => $this->transformeId($element->_id),
                                "name" => $title,
                                "private" => ($index == 1),
                                "tenant" => $tenant,
                            ];
                        }
                    }
                }
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                $this->logger->error('leadwire.es.getRawDashboards', ['error' => $e->getMessage()]);
            }
        }
        return $res;
    }

    protected function filter($dashboards)
    {
        $custom = [];
        $default = [];
        foreach ($dashboards['Custom'] as $item) {
            preg_match_all('/\[([^]]+)\]/', $item['name'], $out);
            $theme = isset($out[1][0]) === true ? $out[1][0] : 'Misc';
            $custom[$theme][] = [
                "private" => $item['private'],
                "id" => $item['id'],
                "tenant" => $item['tenant'],
                "name" => str_replace("[$theme] ", "", $item['name']),
            ];
        }

        foreach ($dashboards['Default'] as $item) {
            preg_match_all('/\[([^]]+)\]/', $item['name'], $out);
            $theme = isset($out[1][0]) === true ? $out[1][0] : 'Misc';
            $default[] = [
                "private" => $item['private'],
                "id" => $item['id'],
                "tenant" => $item['tenant'],
                "name" => str_replace("[$theme] ", "", $item['name']),
            ];
        }

        return [
            "Default" => $default,
            "Custom" => $custom,
        ];
    }

    protected function transformeId($id)
    {
        $id = str_replace('dashboard:', "", $id);
        $id = str_replace('visualization:', "", $id);

        return $id;
    }

    private function getAuth()
    {
        return [
            $this->settings['username'],
            $this->settings['password'],
        ];
    }
}
