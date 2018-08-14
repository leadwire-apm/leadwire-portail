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

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->settings = $container->getParameter('kibana');
        $this->logger = $logger;
    }

    /**
     * @param App $app
     * @return bool
     */
    public function createDashboards(App $app)
    {
        $client = new \GuzzleHttp\Client(['defaults' => ['verify' => false]]);
        $json_template = $this->prepareTemplate($app->getType());
        try {
            $response = $client->request(
                'POST',
                str_replace('{{tenant}}', $app->getUuid(), $this->settings['inject_dashboards']),
                [
                    'body' => $json_template,
                    'headers' => [
                        'Content-type'  => 'application/json',
                        'kbn-xsrf' => 'true',
                    ],
                    'auth' => [
                        $this->settings['username'],
                        $this->settings['password']
                    ]
                ]
            );
            return true;
        } catch (GuzzleException $e) {
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
}
