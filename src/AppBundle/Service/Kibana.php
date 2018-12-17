<?php
namespace AppBundle\Service;

use AppBundle\Document\App;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Kibana Service. Manage connexions with Kibana Rest API.
 * @package AppBundle\Service
 * @author Anis Ksontini <aksontini@ats-digital.com>
 */
class Kibana
{

    private $settings;
    private $logger;
    private $elastic;

    public function __construct(ContainerInterface $container, LoggerInterface $logger, ElasticSearch $elastic)
    {
        $this->settings = $container->getParameter('kibana');
        $this->logger = $logger;
        $this->elastic = $elastic;
    }

    /**
     * @param App $app
     * @return bool
     */
    public function createDashboards(App $app)
    {
        $isSuccess = $this->elastic->deleteIndex();
        //$this->elastic->importIndex($app->getIndex());
        $isSuccess &= $this->elastic->resetAppIndexes($app);

        $client = new \GuzzleHttp\Client(['defaults' => ['verify' => false]]);
        $json_template = json_encode($app->getType()->getTemplate());
        $url = $this->settings['host'] . "/api/kibana/dashboards/import";

        try {
            $response = $client->post(
                $url,
                [
                    'body' => $json_template,
                    'headers' => [
                        'Content-type' => 'application/json',
                        'kbn-xsrf' => 'true',
                        'x-tenants-enabled' => true,
                    ],
                    'auth' => $this->getAuth(),
                ]
            );

            $isSuccess &= $this->elastic->copyIndex($app->getIndex());
            return $isSuccess;
        } catch (\Exception $e) {
            $this->logger->error("error on import", ["exception" => $e]);
            return false;
        }
    }

    private function getAuth()
    {
        return [
            $this->settings['username'],
            $this->settings['password'],
        ];
    }
}
