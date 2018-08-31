<?php

namespace AppBundle\Service;

use AppBundle\Document\App;
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
        //$uuid = "test";
        $userUuid = $app->getOwner()->getUuid();
        $appUuid = $app->getUuid();
        // for prod use only
        $tenants = ["default" => "app_$appUuid", "user" => "user_$userUuid", "shared" => "share_$appUuid"];
        // for dev use only
//        $tenants = ["default" => "apptest", 'user' => "adm-portail", "shared" => "share_$appUuid"];
        $res = [];
        //$this->resetIndex($app);
        foreach ($tenants as $name => $tenant) {
            try {
                $key = $name == "default" ? "Default" : "Custom";
                $res[$key] = isset($res[$key]) ? $res[$key] : [];
                $response = $client->get(
                    $this->settings['host'] . ".kibana_$tenant" . "/_search?pretty",
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
                            "private" => ($key == "user" || $key == "default"),
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
        foreach ($dashboards['Custom'] as $item) {
            preg_match_all('/\[([^]]+)\]/', $item['name'], $out);
            $theme = isset($out[1][0]) ? $out[1][0] : 'Musc';
            $custom[$theme][] = [
                "private" => $item['private'],
                "id" => $item['id'],
                "name" => str_replace("[$theme] ", "", $item['name']),
            ];
        }
        return [
            "Default" => $dashboards['Default'],
            "Custom" => $custom,
        ];
    }

    /**
     * @param App $app
     */
    public function resetIndex(App $app)
    {
        $client = new \GuzzleHttp\Client(['defaults' => ['verify' => false]]);
        $userUuid = $app->getOwner()->getUuid();
        $appUuid = $app->getUuid();
        $tenants = ["default" => "app_$appUuid", "user" => "user_$userUuid", "shared" => "share_$appUuid"];
        try {
            foreach ($tenants as $name => $tenant) {
                $client->put(
                    $this->settings['host'] . ".kibana_$tenant",
                    [
                        'headers' => [
                            'Content-type' => 'application/json',
                        ],
                        'auth' => $this->getAuth()
                    ]
                );
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->logger->error("Error on reset index", ['exception' => $e ]);
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
            $this->logger->error("Error when replacing indexes", ['exception' => $e ]);
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
