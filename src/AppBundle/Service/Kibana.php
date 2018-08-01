<?php
namespace AppBundle\Service;

use AppBundle\Document\App;
use GuzzleHttp\Exception\GuzzleException;

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

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->settings = $container->getParameter('kibana');
        $this->logger = $logger;
    }

    public function createDashboards(App $app)
    {
        $client = new \GuzzleHttp\Client(['defaults' => ['verify' => false]]);
        $json_template = file_get_contents($this->settings['template_folder'] . '/' . $app->getType() . '.json');
        try {
            $response = $client->request(
                'POST',
                $this->settings['inject_dashboards'],
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
            sd($e->getMessage());
            return false;
        }
    }
}
