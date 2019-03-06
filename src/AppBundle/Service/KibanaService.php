<?php
namespace AppBundle\Service;

use GuzzleHttp\Client;
use AppBundle\Document\User;
use Psr\Log\LoggerInterface;
use AppBundle\Service\JWTHelper;
use AppBundle\Document\Application;
use AppBundle\Manager\TemplateManager;
use AppBundle\Document\ApplicationType;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Kibana Service. Manage connexions with Kibana Rest API.
 * @package AppBundle\Service
 * @author Anis Ksontini <aksontini@ats-digital.com>
 *
 * Note:
 *  * ALL communication with Kibana is done with a JWT token in Authorization header
 *  * ALL communication with ElasticSearch is done with Basic Auth
 *
 */
class KibanaService
{
    /**
     * @var array
     */
    private $settings;

    /**
     * @var string
     */
    private $url;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ElasticSearchService
     */
    private $elastic;

    /**
     * @var TemplateManager
     */
    private $templateManager;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var JWTHelper
     */
    private $jwtHelper;

    public function __construct(
        LoggerInterface $logger,
        ElasticSearchService $elastic,
        TemplateManager $templateManager,
        JWTHelper $jwtHelper,
        array $settings = []
    ) {
        $this->settings = $settings;
        $this->logger = $logger;
        $this->elastic = $elastic;
        $this->templateManager = $templateManager;
        $this->jwtHelper = $jwtHelper;
        $this->httpClient = new Client(['defaults' => ['verify' => false]]);
        $this->url = $settings['host'] . ":" . (string) $settings['port'] . "/";
    }

    /**
     * * curl --insecure -u $es_admin_user:$es_admin_password  -X POST "$protocol://$host:$port/api/kibana/dashboards/import?exclude=index-pattern&force=true" -H 'kbn-xsrf: true' -H "tenant:$tenant_name" -H 'Content-Type: application/json' -d @/home/centos/pack_curl/apm-dashboards$$.json
     *
     * @param string $applicationName
     * @param string $tenantName
     * @param string $template
     * @param ApplicationType $applicationType
     *
     * @return bool
     */
    private function createDashboards(string $applicationName, string $tenantName, string $template, ApplicationType $applicationType)
    {
        $template = $this->templateManager->getOneBy(
            [
                'applicationType.id' => $applicationType->getId(),
                'name' => $template,
            ]
        );

        if ($template !== null) {
            $content = str_replace("__replace_token__", $applicationName, $template->getContent());
            $content = str_replace("__replace_service__", $applicationName, $content);

            $response = $this->httpClient->post(
                $this->url . "/api/kibana/dashboards/import?exclude=index-pattern&force=true",
                [
                    'headers' => [
                        'kbn-xsrf' => true,
                        'tenant' => $tenantName,
                        'Content-Type' => 'application/json',
                    ],
                    'body' => $content,
                    'auth' => $this->getAuth()
                ]
            );

            return $response->getStatusCode() === Response::HTTP_OK;
        } else {
            throw new \Exception("Template (apm-dashboard) not found");
        }
    }

    /**
     *
     * @param string $applicationName
     * @param string $tenantName
     * @param ApplicationType $applicationType
     *
     * @return bool
     */
    public function createAllTenantDashboards(string $applicationName, string $tenantName, ApplicationType $applicationType): bool
    {
        return $this->createDashboards($applicationName, $tenantName, "apm-dashboards-all", $applicationType);
    }

    /**
     *
     * @param string $applicationName
     * @param string $tenantName
     * @param ApplicationType $applicationType
     *
     * @return bool
     */
    public function createTenantDashboards(string $applicationName, string $tenantName, ApplicationType $applicationType): bool
    {
        return $this->createDashboards($applicationName, $tenantName, "apm-dashboards", $applicationType);
    }

    /**
     * * curl --insecure -H "Authorization: Bearer ${authorization}" -X POST "$protocol://$host:$port/api/saved_objects/index-pattern/$appname" -H 'kbn-xsrf: true' -H 'Content-Type: application/json' -d @/home/centos/pack_curl/apmserver$$.json
     *
     * @return bool
     */
    public function loadIndexPattern(Application $application, string $tenant, ?User $user = null): bool
    {
        $template = $this->templateManager->getOneBy(
            [
                'applicationType.id' => $application->getType()->getId(),
                'name' => 'apmserver',
            ]
        );

        if ($template !== null) {
            $content = str_replace("__replace_token__", $application->getName(), $template->getContent());

            if ($user === null) {
                $authorization = null;
            } else {
                $authorization = $this->jwtHelper->getAuthorizationHeader($user);
            }

            $response = $this->httpClient->post(
                $this->url . "/api/saved_objects/index-pattern/{$application->getName()}",
                [
                    'headers' => [
                        'kbn-xsrf' => true,
                        'Content-Type' => 'application/json',
                        'Authorization' => "Bearer $authorization",
                        'tenant' => $tenant
                    ],
                    'body' => $content,
                ]
            );

            return $response->getStatusCode() === Response::HTTP_OK;
        } else {
            throw new \Exception("Template (apmserver) not found");
        }
    }

    /**
     * * curl --insecure  -H "Authorization: Bearer ${authorization}"  -XGET "https://kibana.leadwire.io/api/saved_objects/index-pattern/${appname}" -H 'kbn-xsrf: true' -H 'Content-Type: application/json'
     *
     * @param string $applicationName
     * @param User $user
     *
     * @return bool
     */
    public function checkIndexPattern(string $applicationName, User $user): bool
    {
        $authorization = $this->jwtHelper->getAuthorizationHeader($user);
        $response = $this->httpClient->get(
            $this->url . "/api/saved_objects/index-pattern/$applicationName",
            [
                'headers' => [
                    'kbn-xsrf' => true,
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer $authorization"
                ]
            ]
        );

        return $response->getStatusCode() === Response::HTTP_OK;
    }

    /**
     * * curl --insecure -H "Authorization: Bearer ${authorization}" -X POST "$protocol://$host:$port/api/kibana/settings/defaultIndex"  -d"{\"value\":\"$appname\"}" -H 'kbn-xsrf: true' -H 'Content-Type: application/json'
     *
     * @param string $applicationName
     * @param USer $user
     *
     * @return void
     */
    public function makeDefaultIndex(string $applicationName, User $user)
    {
        $authorization = $this->jwtHelper->getAuthorizationHeader($user);
        $response = $this->httpClient->post(
            $this->url . "/api/kibana/settings/defaultIndex",
            [
                'headers' => [
                    'kbn-xsrf' => true,
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer $authorization"
                ],
                'body' => [
                    'value' => $applicationName
                ],
            ]
        );
    }

    private function getAuth()
    {
        return [
            $this->settings['username'],
            $this->settings['password'],
        ];
    }
}
