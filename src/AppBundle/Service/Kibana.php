<?php
namespace AppBundle\Service;

use AppBundle\Document\App;
use GuzzleHttp\Exception\GuzzleException;

use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
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
        //$this->elastic->deleteIndex();

        $client = new \GuzzleHttp\Client(['defaults' => ['verify' => false]]);
        //$json_template = $this->prepareTemplate($app->getType());
        $json_template = json_encode($app->getType()->getTemplate());
        $url = $this->settings['base_url'] . $app->getIndex() . "/api/kibana/dashboards/import";
//        $url = str_replace(
//            '{{tenant}}',
//            ',
//            $this->settings['inject_dashboards']
//        );

        try {
            $response = $client->post(
                $url,
                [
                    'body' => $json_template,
                    'headers' => [
                        //'Content-type'  => 'application/json',
                        'kbn-xsrf' => 'true',
                    ],
                    'auth' => $this->getAuth()
                ]
            );
            return $this->elastic->copyIndex($app->getIndex());
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }

    private function prepareTemplate($template)
    {
        $template = preg_replace_callback('/__uuid__/', function ($maches) {
            $uuid = Uuid::uuid1();
            return $uuid->toString();
        }, json_encode($template));
        return $template;
    }

    private function getAuth()
    {
        return  [
            $this->settings['username'],
            $this->settings['password']
        ];
    }
}
