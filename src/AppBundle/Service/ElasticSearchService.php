<?php

namespace AppBundle\Service;

use AppBundle\Document\Application;
use AppBundle\Document\User;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use SensioLabs\Security\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ElasticSearchService Service. Manage connexions with Kibana Rest API.
 * @package AppBundle\Service
 * @author Anis Ksontini <aksontini@ats-digital.com>
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
    private $env;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * ElasticSearchService constructor.
     * @param LoggerInterface $logger
     * @param string $env
     * @param array $settings
     */
    public function __construct(LoggerInterface $logger, string $env, array $settings = [])
    {
        $this->settings = $settings;
        $this->logger = $logger;
        $this->env = $env;
        $this->httpClient = new Client(['defaults' => ['verify' => false]]);
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

    /**
     * @param Application $app
     */
    protected function getRawDashboards(Application $app)
    {
        $res = [];

        $client = new Client(['defaults' => ['verify' => false]]);

        // for prod use only
        if ($this->env === 'prod') {
            $tenants = $app->getIndexes();
        } else {
            // for dev use only
            $tenants = ["apptest", "adm-portail", "share_" . $app->getUuid()];
            $tenants = $app->getIndexes();
        }

        foreach ($tenants as $index => $tenant) {
            try {
                $key = $index === 0 ? "Default" : "Custom";
                $res[$key] = isset($res[$key]) === true ? $res[$key] : [];
                $response = $client->get(
                    $this->settings['host'] . ".kibana_$tenant" . "/_search?pretty&from=0&size=10000",
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
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                $this->logger->error($e->getMessage());
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

    /**
     * @param Application $app
     *
     * @return bool
     */
    public function resetAppIndexes(Application $app)
    {
        $tenants = $app->getIndexes();

        if (false === $this->postIndex(
            [
                "indices" => ".kibana_app",
                "ignore_unavailable" => "true",
                "include_global_state" => false,
                "rename_pattern" => ".kibana_(.+)",
                "rename_replacement" => ".kibana_" . $tenants[0],
            ]
        ) ||
            false === $this->postIndex(
                [
                    "indices" => ".kibana_shared",
                    "ignore_unavailable" => "true",
                    "include_global_state" => false,
                    "rename_pattern" => ".kibana_(.+)",
                    "rename_replacement" => ".kibana_" . $tenants[2],
                ]
            )) {
            return false;
        }
        return true;
    }

    /**
     * @param User $user
     */
    public function resetUserIndexes(User $user)
    {
        $this->postIndex(
            [
                "indices" => ".kibana_user",
                "ignore_unavailable" => "true",
                "include_global_state" => false,
                "rename_pattern" => ".kibana_(.+)",
                "rename_replacement" => ".kibana_" . $user->getIndex(),
            ]
        );
    }

    public function importIndex(string $index = "adm-portail")
    {
        $client = new Client(['defaults' => ['verify' => false]]);
        try {
            $client->post(
                $this->settings['host'] . ".kibana_" . $index . "/doc/index-pattern:apm-*",
                [
                    'body' => json_encode(
                        [
                            "type" => "index-pattern",
                            "index-pattern" => [
                                "title" => "apm-*",
                                "timeFieldName" => "@timestamp",
                            ],
                        ]
                    ),
                    'headers' => [
                        'Content-type' => 'application/json',
                    ],
                    'auth' => $this->getAuth(),
                ]
            );
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->logger->critical("Error on Import index", ['exception' => $e]);
        }
    }

    private function postIndex(array $options)
    {
        $client = new Client(['defaults' => ['verify' => false]]);
        try {
            $client->post(
                $this->settings['host'] . "/_snapshot/my_backup/kibana_snapshot/_restore",
                [
                    'body' => json_encode($options),
                    'headers' => [
                        'Content-type' => 'application/json',
                    ],
                    'auth' => $this->getAuth(),
                ]
            );
            return true;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->logger->critical("Error on reset index", ['exception' => $e]);
            return false;
        }
    }

    protected function transformeId($id)
    {
        $searchs = ['dashboard:', 'visualization:'];
        foreach ($searchs as $search) {
            $id = str_replace($search, "", $id);
        }
        return $id;
    }

    /**
     * @deprecated
     *
     * Kept for history
     *
     */
    // public function deleteIndex()
    // {
    //     try {
    //         $client = new Client(['defaults' => ['verify' => false]]);
    //         $client->delete(
    //             $this->settings['host'] . ".kibana_adm-portail",
    //             [
    //                 'auth' => $this->getAuth(),
    //             ]
    //         );
    //         return true;
    //     } catch (\Exception $e) {
    //         $this->logger->warning("Error when deleting index", ['exception' => $e]);
    //         return false;
    //     }
    // }

    public function copyIndex($index)
    {
        try {
            $client = new Client(['defaults' => ['verify' => false]]);
            $client->post(
                $this->settings['host'] . "_reindex",
                [
                    'body' => json_encode(
                        [
                            'source' => [
                                "index" => ".kibana_adm-portail",
                            ],
                            'dest' => [
                                "index" => ".kibana_$index",
                            ],
                        ]
                    ),
                    'headers' => [
                        'Content-type' => 'application/json',

                    ],
                    'auth' => $this->getAuth(),
                ]
            );
            return true;
        } catch (\Exception $e) {
            $this->logger->critical("Error when replacing indexes", ['exception' => $e]);
            return false;
        }
    }

    public function getAuth()
    {
        return [
            $this->settings['username'],
            $this->settings['password'],
        ];
    }

    /**
     * curl --insecure -u $es_admin_user:$es_admin_password -XGET https://es.leadwire.io/.kibana_${tenant_name}
     *
     * Returns the content of the index if it is found, FALSE otherwise
     *
     * @param string $tenantName
     *
     * @return bool|string
     */
    public function getIndex(string $tenantName)
    {
        $response = $this->httpClient->get(
            $this->settings['host'] . ".kibana_$tenantName",
            [
                'auth' => $this->getAuth(),
            ]
        );

        if ($response->getStatusCode() === Response::HTTP_OK) {
            return $response->getBody()->getContents();
        } elseif ($response->getStatusCode() === Response::HTTP_NOT_FOUND) {
            return false;
        } else {
            throw new \Exception("Got {$response->getStatusCode()} from Guzzle", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * curl --insecure -u $es_admin_user:$es_admin_password -XDELETE https://es.leadwire.io/.kibana_${tenant_name}
     *
     * @param string $tenantName
     *
     * @return bool
     */
    public function deleteIndex(string $tenantName): bool
    {
        $response = $this->httpClient->delete(
            $this->settings['host'] . ".kibana_$tenantName",
            [
                'auth' => $this->getAuth(),
            ]
        );

        return true;
        // TODO: Some checks here for success/fail
    }

    public function createAlias()
    {
    }
}
