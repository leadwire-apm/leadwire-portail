<?php

namespace AppBundle\Service;

use AppBundle\Document\App;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
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
        $client = new \GuzzleHttp\Client(['defaults' => ['verify' => false]]);
        //$uuid = $app->getUuid();
        $uuid = "apptest";
        try {
            $response = $client->get(
                $this->settings['host'] . ".kibana_$uuid" . "/_search?pretty",
                [
                    'headers' => [
                        'Content-type'  => 'application/json',
                    ],
                    'auth' => [
                        $this->settings['username'],
                        $this->settings['password']
                    ]
                ]
            );
            return json_decode($response->getBody());
        } catch (GuzzleException $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }
}
