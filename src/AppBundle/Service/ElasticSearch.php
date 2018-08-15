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
            $client = new \GuzzleHttp\Client(['defaults' => ['verify' => false]]);//$uuid = $app->getUuid();
            $uuid = "apptest";
            $response = $client->get(
                $this->settings['host'] . ".kibana_$uuid" . "/_search?pretty",
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
            $res = [];
            foreach ($body as $element) {
                if (strpos($element->_id, "dashboard:") !== false) {
                    $res [] = [
                        "id" => str_replace("dashboard:", "", $element->_id),
                        "name" => $element->_source->dashboard->title,
                    ];
                }
            }
            return $res;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
                $this->logger->error($e->getMessage());
                throw new HttpException("An error has occured while executing your request.", 500);
        }
    }
}
