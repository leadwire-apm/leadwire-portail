<?php

namespace AppBundle\Service;

use AppBundle\Document\App;
use AppBundle\Document\User;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use SensioLabs\Security\Exception\HttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ElasticSearch Service. Manage connexions with Kibana Rest API.
 * @package AppBundle\Service
 * @author Anis Ksontini <aksontini@ats-digital.com>
 */
class ElasticSearch
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
     * ElasticSearch constructor.
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->settings = $container->getParameter('elastic');
        $this->logger = $logger;
    }

    public function getDashboads(App $app)
    {
        try {
            return $this->filter($this->getRawDashboards($app));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new HttpException("An error has occurred while executing your request.", 500);
        }
    }

    protected function getRawDashboards(App $app)
    {
        $client = new \GuzzleHttp\Client(['defaults' => ['verify' => false]]);
        // for prod use only
        $tenants = $app->getIndexes();
        // for dev use only
//        $tenants = ["default" => "apptest", 'user' => "adm-portail", "shared" => "share_$appUuid"];
        $res = [];

        foreach ($tenants as $index => $tenant) {
            try {
                $key = $index == 0 ? "Default" : "Custom";
                $res[$key] = isset($res[$key]) ? $res[$key] : [];
                $response = $client->get(
                    $this->settings['host'] . ".kibana_$tenant" . "/_search?pretty&from=0&size=10000",
                    [
                        'headers' => [
                            'Content-type' => 'application/json',
                        ],
                        'auth' => [
                            $this->settings['username'],
                            $this->settings['password']
                        ]
                    ]
                );

                $body = json_decode($response->getBody())->hits->hits;
                foreach ($body as $element) {
                    if (in_array($element->_source->type, array("dashboard"/*, "visualization"*/))) {
                        $title = $element->_source->{$element->_source->type}->title;

                        $res [$key][] = [
                            "id" => $this->transformeId($element->_id),
                            "name" => $title,
                            "private" => ($index == 1),
                            "tenant" => $tenant
                        ];
                    }
                }
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                $this->logger->error($e->getMessage());
                //throw new HttpException("An error has occurred while executing your request.", 500);
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
            $theme = isset($out[1][0]) ? $out[1][0] : 'Misc';
            $custom[$theme][] = [
                "private" => $item['private'],
                "id" => $item['id'],
                "tenant" => $item['tenant'],
                "name" => str_replace("[$theme] ", "", $item['name']),
            ];
        }

        foreach ($dashboards['Default'] as $item) {
            preg_match_all('/\[([^]]+)\]/', $item['name'], $out);
            $theme = isset($out[1][0]) ? $out[1][0] : 'Misc';
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
     * @param App $app
     * @return bool
     */
    public function resetAppIndexes(App $app)
    {
        $tenants = $app->getIndexes();

        if (!$this->postIndex(
            [
                    "indices" => ".kibana_app",
                    "ignore_unavailable" => "true",
                    "include_global_state" => false,
                    "rename_pattern" => ".kibana_(.+)",
                    "rename_replacement" => ".kibana_" . $tenants[0]
            ]
        ) ||
            ! $this->postIndex(
                [
                    "indices" => ".kibana_shared",
                    "ignore_unavailable" => "true",
                    "include_global_state" => false,
                    "rename_pattern" => ".kibana_(.+)",
                    "rename_replacement" => ".kibana_" . $tenants[2]
                ]
            ) ) {
            return false;
        }
        return true;
    }

    public function resetUserIndexes(User $user)
    {
        $this->postIndex(
            [
                "indices" => ".kibana_user",
                "ignore_unavailable" => "true",
                "include_global_state" => false,
                "rename_pattern" => ".kibana_(.+)",
                "rename_replacement" => ".kibana_" . $user->getIndex()
            ]
        );
    }

    public function importIndex(string $index = "adm-portail")
    {
        $client = new \GuzzleHttp\Client(['defaults' => ['verify' => false]]);
        try {
            $client->post(
                $this->settings['host'] . ".kibana_" . $index . "/doc/index-pattern:apm-*",
                [
                    'body' => json_encode([
                        "type" => "index-pattern",
                        "index-pattern" => [
                            "title" => "apm-*",
                            "timeFieldName" => "@timestamp",
                        ]
                    ]),
                    'headers' => [
                        'Content-type' => 'application/json',
                    ],
                    'auth' => $this->getAuth()
                ]
            );
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->logger->critical("Error on Import index", ['exception' => $e ]);
        }
    }

    private function putIndex(string $index)
    {
        $client = new \GuzzleHttp\Client(['defaults' => ['verify' => false]]);
        try {
            $client->put(
                $this->settings['host'] . ".kibana_" . $index,
                [
                    'headers' => [
                        'Content-type' => 'application/json',
                    ],
                    'auth' => $this->getAuth()
                ]
            );
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->logger->critical("Error on reset index", ['exception' => $e ]);
        }
    }

    private function postIndex(array $options)
    {
        $client = new \GuzzleHttp\Client(['defaults' => ['verify' => false]]);
        try {
            $client->post(
                $this->settings['host'] . "/_snapshot/my_backup/kibana_snapshot/_restore",
                [
                    'body' => json_encode($options),
                    'headers' => [
                        'Content-type' => 'application/json',
                    ],
                    'auth' => $this->getAuth()
                ]
            );
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->logger->critical("Error on reset index", ['exception' => $e ]);
            return false;
        }
    }

    protected function transformeId($id)
    {
        $searchs = ['dashboard:', 'visualization:'];
        foreach ($searchs as $search) {
            $id = str_replace($search, "", $id);
        }
        return $id ;
    }

    public function deleteIndex()
    {
        try {
            $client = new \GuzzleHttp\Client(['defaults' => ['verify' => false]]);
            $response = $client->delete($this->settings['host'] . ".kibana_adm-portail", [
                'auth' => $this->getAuth()
            ]);
            return true;
        } catch (\Exception $e) {
            $this->logger->warning("Error when deleting index", ['exception' => $e ]);
            return false;
        }
    }

    public function copyIndex($index)
    {
        try {
            $client = new \GuzzleHttp\Client(['defaults' => ['verify' => false]]);
            $response = $client->post($this->settings['host']  . "_reindex", [
                'body' => json_encode([
                    'source' => [
                        "index" => ".kibana_adm-portail"
                    ],
                    'dest' => [
                        "index" => ".kibana_$index"
                    ]
                ]),
                'headers' => [
                    'Content-type'  => 'application/json',

                ],
                'auth' => $this->getAuth()
            ]);
            return true;
        } catch (\Exception $e) {
            $this->logger->critical("Error when replacing indexes", ['exception' => $e ]);
            return false;
        }
    }

    public function getAuth()
    {
        return  [
            $this->settings['username'],
            $this->settings['password']
        ];
    }
}
