<?php
namespace AppBundle\Service;

use AppBundle\Document\Application;
use AppBundle\Document\User;
use AppBundle\Manager\ApplicationManager;
use AppBundle\Manager\TemplateManager;
use AppBundle\Service\JWTHelper;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
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
     * @var ApplicationManager $applicationManager
     */
    private $applicationManager;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var JWTHelper
     */
    private $jwtHelper;

    /**
     * Undocumented function
     *
     * @param LoggerInterface $logger
     * @param ElasticSearchService $elastic
     * @param TemplateManager $templateManager
     * @param ApplicationManager $applicationManager
     * @param JWTHelper $jwtHelper
     * @param array $settings
     */
    public function __construct(
        LoggerInterface $logger,
        ElasticSearchService $elastic,
        TemplateManager $templateManager,
        ApplicationManager $applicationManager,
        JWTHelper $jwtHelper,
        array $settings = []
    ) {
        $this->settings = $settings;
        $this->logger = $logger;
        $this->elastic = $elastic;
        $this->templateManager = $templateManager;
        $this->applicationManager = $applicationManager;
        $this->jwtHelper = $jwtHelper;
        $this->httpClient = new Client(
            [
                'curl' => array(CURLOPT_SSL_VERIFYPEER => false),
                'verify' => false,
                'http_errors' => false
            ]
        );
        $this->url = $settings['host'] . ":" . (string) $settings['port'] . "/";
    }

    public function createAllUserDashboard(User $user)
    {
        // TODO CHANGE THIS HACK
        $application = $this->applicationManager->getDemoApplications()[0];

        $template = $this->templateManager->getOneBy(
            [
                'applicationType.id' => $application->getType()->getId(),
                'name' => 'apm-dashboards-all',
            ]
        );

        if ($template !== null) {
            $content = str_replace("__replace_token__", 'all_user_' . $user->getUuid(), $template->getContent());
            $content = str_replace("__replace_service__", 'all_user_' . $user->getUuid(), $content);

            $authorization = $this->jwtHelper->getAuthorizationHeader(); // Default use leadwire-apm user

            $headers = [
                'kbn-xsrf' => true,
                'Content-Type' => 'application/json',
                'tenant' => "all_user_{$user->getUuid()}",
                'X-Proxy-User' => "user_{$user->getUuid()}",
                'Authorization' => "Bearer $authorization",
            ];

            $response = $this->httpClient->post(
                $this->url . "api/kibana/dashboards/import?exclude=index-pattern&force=true",
                [
                    'headers' => $headers,
                    'body' => $content,
                ]
            );

            $this->logger->notice(
                "leadwire.kibana.createAllUserDashboard",
                [
                    'url' => $this->url . "api/kibana/dashboards/import?exclude=index-pattern&force=true",
                    'verb' => 'POST',
                    'headers' => $headers,
                    // 'body' => $content,
                    'template' => $template->getName(),
                    'status_code' => $response->getStatusCode(),
                ]
            );

            return $response->getStatusCode() === Response::HTTP_OK;
        } else {
            throw new \Exception("Template ($template) not found");
        }
    }

    /**
     *
     * @param Application $application
     * @param User $user
     * @param boolean $shared
     *
     * @return boolean
     */
    public function createApplicationDashboards(Application $application, User $user, $shared = false): bool
    {
        if ($shared === true) {
            $prefix = "shared_";
            $authorization = $this->jwtHelper->getAuthorizationHeader(); // Default use leadwire-apm user
        } else {
            $prefix = "app_";
            $authorization = $this->jwtHelper->getAuthorizationHeader($user);
        }

        $template = $this->templateManager->getOneBy(
            [
                'applicationType.id' => $application->getType()->getId(),
                'name' => 'apm-dashboards',
            ]
        );

        if ($template !== null) {
            $content = str_replace("__replace_token__", $prefix . $application->getName(), $template->getContent());
            $content = str_replace("__replace_service__", $prefix . $application->getName(), $content);

            $headers = [
                'kbn-xsrf' => true,
                'Content-Type' => 'application/json',
                'tenant' => "{$prefix}{$application->getUuid()}",
                'X-Proxy-User' => "user_{$user->getUuid()}",
                'Authorization' => "Bearer $authorization",
            ];

            $response = $this->httpClient->post(
                $this->url . "api/kibana/dashboards/import?exclude=index-pattern&force=true",
                [
                    'headers' => $headers,
                    'body' => $content,
                ]
            );

            $this->logger->notice(
                "leadwire.kibana.createApplicationDashboards",
                [
                    'url' => $this->url . "api/kibana/dashboards/import?exclude=index-pattern&force=true",
                    'verb' => 'POST',
                    'headers' => $headers,
                    'status_code' => $response->getStatusCode(),
                ]
            );

            return $response->getStatusCode() === Response::HTTP_OK;
        } else {
            $this->logger->critical(
                "leadwire.kibana.createApplicationDashboards",
                [
                    'error' => "Template ($template) not found",
                ]
            );
            throw new \Exception("Template ($template) not found");
        }
    }

    public function loadIndexPatternForAllUser(User $user)
    {
        // TODO CHANGE THIS HACK
        $application = $this->applicationManager->getDemoApplications()[0];

        $template = $this->templateManager->getOneBy(
            [
                'applicationType.id' => $application->getType()->getId(),
                'name' => 'apmserver',
            ]
        );

        if ($template !== null) {
            $content = str_replace("__replace_token__", "all_user_{$user->getUuid()}", $template->getContent());
            $authorization = $this->jwtHelper->getAuthorizationHeader($user);
            $headers = [
                'kbn-xsrf' => true,
                'Content-Type' => 'application/json',
                'tenant' => "all_user_{$user->getUuid()}",
                'X-Proxy-User' => "user_{$user->getUuid()}",
                'Authorization' => "Bearer $authorization",
            ];

            $response = $this->httpClient->post(
                $this->url . "api/saved_objects/index-pattern/{$application->getName()}",
                [
                    'headers' => $headers,
                    'body' => $content,
                ]
            );

            $this->logger->notice(
                "leadwire.kibana.loadIndexPatternForAllUser",
                [
                    'url' => $this->url . "api/saved_objects/index-pattern/{$application->getName()}",
                    'verb' => 'POST',
                    'headers' => $headers,
                    'status_code' => $response->getStatusCode(),
                ]
            );
        }
    }

    /**
     * * curl --insecure -H "Authorization: Bearer ${authorization}" -X POST "$protocol://$host:$port/api/saved_objects/index-pattern/$appname" -H 'kbn-xsrf: true' -H 'Content-Type: application/json' -d @/home/centos/pack_curl/apmserver$$.json
     *
     * @return bool
     */
    public function loadIndexPatternForApplication(Application $application, User $user, string $tenant, bool $shared = false): bool
    {
        $template = $this->templateManager->getOneBy(
            [
                'applicationType.id' => $application->getType()->getId(),
                'name' => 'apmserver',
            ]
        );

        if ($template !== null) {
            $content = str_replace("__replace_token__", $application->getName(), $template->getContent());

            $headers = [
                'kbn-xsrf' => true,
                'Content-Type' => 'application/json',
                'tenant' => $tenant,
                'X-Proxy-User' => "user_{$user->getUuid()}",
            ];

            if ($shared === true) {
                $authorization = $this->jwtHelper->getAuthorizationHeader(); // Default use leadwire-apm user
            } else {
                $authorization = $this->jwtHelper->getAuthorizationHeader($user);
            }

            $headers['Authorization'] = "Bearer $authorization";

            $response = $this->httpClient->post(
                $this->url . "api/saved_objects/index-pattern/{$application->getName()}",
                [
                    'headers' => $headers,
                    'body' => $content,
                ]
            );

            $this->logger->notice(
                "leadwire.kibana.loadIndexPatternForApplication",
                [
                    'url' => $this->url . "api/saved_objects/index-pattern/{$application->getName()}",
                    'verb' => 'POST',
                    'headers' => $headers,
                    'status_code' => $response->getStatusCode(),
                ]
            );

            return true;
        } else {
            throw new \Exception("Template (apmserver) not found");
        }
    }

    public function loadIndexPatternForDemoApplications(User $user)
    {
        $demoApplications = $this->applicationManager->getDemoApplications();
        foreach ($demoApplications as $demoApplication) {
            $this->loadIndexPatternForApplication($demoApplication, $user, "user_{$user->getUuid()}", false);
        }

        // $this->makeDefaultIndex('jpetstore', $user);
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
        $headers = [
            'kbn-xsrf' => true,
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer $authorization",
        ];

        $response = $this->httpClient->get(
            $this->url . "/api/saved_objects/index-pattern/$applicationName",
            [
                'headers' => $headers,
            ]
        );

        $this->logger->notice(
            "leadwire.kibana.checkIndexPattern",
            [
                'url' => $this->url . "/api/saved_objects/index-pattern/$applicationName",
                'verb' => 'GET',
                'headers' => $headers,
                'status_code' => $response->getStatusCode(),
            ]
        );

        return $response->getStatusCode() === Response::HTTP_OK;
    }

    /**
     * * curl --insecure -H "Authorization: Bearer ${authorization}" -X POST "$protocol://$host:$port/api/kibana/settings/defaultIndex"  -d"{\"value\":\"$appname\"}" -H 'kbn-xsrf: true' -H 'Content-Type: application/json'
     *
     * @param Application $application
     * @param USer $user
     *
     * @return void
     */
    public function makeDefaultIndex(Application $application, User $user)
    {
        $authorization = $this->jwtHelper->getAuthorizationHeader($user);
        $headers = [
            'kbn-xsrf' => true,
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer $authorization",
            'tenant' => "app_{$application->getUuid()}",
            'X-Proxy-User' => "user_{$user->getUuid()}",
        ];
        $content = json_encode(['value' => $application->getName()]);

        $response = $this->httpClient->post(
            $this->url . "api/kibana/settings/defaultIndex",
            [
                'headers' => $headers,
                'body' => $content,
            ]
        );

        $this->logger->notice(
            "leadwire.kibana.makeDefaultIndex",
            [
                'url' => $this->url . "api/kibana/settings/defaultIndex",
                'verb' => 'POST',
                'headers' => $headers,
                'content' => $content,
                'status_code' => $response->getStatusCode(),
            ]
        );
    }
}
