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

    protected function filter($dashboards)
    {
        $custom = [];
        foreach ($dashboards['Custom'] as $item) {
                preg_match_all('/[(.*)]/', $item['name'], $out);
                $theme = isset($out[1][0]) ? $out[1][0] : 'Musc';
                $custom[$theme][] = $item;
        }
        return [
            "Default" => $dashboards['Default'],
            "Custom" => $custom,
        ];
    }

    protected function getRawDashboards(App $app)
    {
        $client = new \GuzzleHttp\Client(['defaults' => ['verify' => false]]);
        //$uuid = "test";
        $userUuid = $app->getOwner()->getUuid();
        $appUuid = $app->getUuid();
        // for prod use only
//        $tenants = ["default" => "app_$appUuid", "user" => "user_$userUuid", "shared" => "share_$appUuid"];
        // for dev use only
        $tenants = ["default" => "apptest", 'user' => "adm-portail", "shared" => "share_$appUuid"];
        $res = [];
        $this->resetIndex($app);
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
                    if (strpos($element->_id, "dashboard:") !== false) {
                        $res [$key][] = [
                            "id" => str_replace("dashboard:", "", $element->_id),
                            "name" => $element->_source->dashboard->title,
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
                        'auth' => [
                            $this->settings['username'],
                            $this->settings['password']
                        ]
                    ]
                );
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->logger->error($e->getMessage());
            //throw new HttpException("An error has occurred while executing your request.", 500);
        }
    }
}
