<?php
namespace AppBundle\Service;

use AppBundle\Document\Application;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

/**
 * Class Kibana Service. Manage connexions with Kibana Rest API.
 * @package AppBundle\Service
 * @author Anis Ksontini <aksontini@ats-digital.com>
 */
class KibanaService
{
    /**
     * @var array
     */
    private $settings;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ElasticSearchService
     */
    private $elastic;

    public function __construct(LoggerInterface $logger, ElasticSearchService $elastic, array $settings = [])
    {
        $this->settings = $settings;
        $this->logger = $logger;
        $this->elastic = $elastic;
    }

    /**
     * @param Application $app
     *
     * @return int|bool
     */
    public function createDashboards(Application $app)
    {
        $isSuccess = $this->elastic->deleteIndex();
        $isSuccess &= $this->elastic->resetAppIndexes($app);

        $client = new Client(['defaults' => ['verify' => false]]);
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
