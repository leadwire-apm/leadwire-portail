<?php
namespace AppBundle\Service;

use AppBundle\Document\Application;
use AppBundle\Document\ApplicationType;
use AppBundle\Document\Template;
use AppBundle\Document\User;
use AppBundle\Manager\ApplicationPermissionManager;
use AppBundle\Manager\ApplicationTypeManager;
use AppBundle\Manager\MonitoringSetManager;
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
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $kibanaAdminUsername;

    /**
     * @var string
     */
    private $kibanaAdminUuid;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TemplateManager
     */
    private $templateManager;

    /**
     * @var ApplicationPermissionManager
     */
    private $permissionManager;

    /**
     * @var ApplicationTypeManager
     */
    private $applicationTypeManager;

    /**
     * @var MonitoringSetManager
     */
    private $msManager;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var JWTHelper
     */
    private $jwtHelper;

    /**
     * @var bool
     */
    private $hasAllUserTenant;

    /**
     * Undocumented function
     *
     * @param LoggerInterface $logger
     * @param TemplateManager $templateManager
     * @param ApplicationPermissionManager $permissionManager,
     * @param ApplicationTypeManager $applicationTypeManager,
     * @param JWTHelper $jwtHelper
     * @param bool $hasAllUserTenant
     * @param array $settings
     */
    public function __construct(
        LoggerInterface $logger,
        TemplateManager $templateManager,
        ApplicationPermissionManager $permissionManager,
        ApplicationTypeManager $applicationTypeManager,
        MonitoringSetManager $msManager,
        JWTHelper $jwtHelper,
        bool $hasAllUserTenant,
        array $settings = []
    ) {
        $this->logger = $logger;
        $this->templateManager = $templateManager;
        $this->permissionManager = $permissionManager;
        $this->applicationTypeManager = $applicationTypeManager;
        $this->msManager = $msManager;
        $this->jwtHelper = $jwtHelper;
        $this->hasAllUserTenant = $hasAllUserTenant;
        $this->httpClient = new Client(
            [
                'curl' => array(CURLOPT_SSL_VERIFYPEER => false),
                'verify' => false,
                'http_errors' => false,
            ]
        );
        $this->url = $settings['host'] . ":" . (string) $settings['port'] . "/";
        $this->kibanaAdminUsername = $settings['kibana_admin_username'];
        $this->kibanaAdminUuid = $settings['kibana_admin_uuid'];
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function createAllUserDashboard(User $user)
    {
        if ($this->hasAllUserTenant === false) {
            return false;
        }

        $defaultType = $this->applicationTypeManager->getOneBy(['name' => ApplicationType::DEFAULT_TYPE]);

        if ($defaultType === null) {
            $this->logger->alert(
                "leadwire.kibana.createAllUserDashboard",
                [
                    "error" => "Java Application Type not found.\nDid you forget to launch bin/console leadwire:install ?",
                ]
            );

            throw new \Exception("Java Application Type not found.");
        }

        $templates = $this->templateManager->getBy(['applicationType.id' => $defaultType->getId()]);

        foreach ($this->msManager->getAll() as $monitoringSet) {
            $filtered = array_filter(
                $templates,
                function (Template $element) use ($monitoringSet) {
                    return $element->getMonitoringSet()->getId() === $monitoringSet->getId() && $element->getType() === Template::DASHBAORDS_ALL;
                }
            );

            /** @var Template|bool $template */
            $template = reset($filtered);

            if (($template instanceof Template) === false) {
                $this->logger->alert(
                    "leadwire.kibana.createAllUserDashboard",
                    [
                        'error' => sprintf("Template (%s) for type (%s) not found", Template::DASHBAORDS_ALL, $defaultType->getName()),
                    ]
                );
                throw new \Exception(sprintf("Template (%s) for type (%s) not found", Template::DASHBAORDS_ALL, $defaultType->getName()));
            }

            $content = str_replace("__replace_token__", $user->getAllUserIndex(), $template->getContent());
            $content = str_replace("__replace_service__", $user->getAllUserIndex(), $content);

            $authorization = $this->jwtHelper->encode($this->kibanaAdminUsername, $this->kibanaAdminUuid);

            $headers = [
                'kbn-xsrf' => true,
                'Content-Type' => 'application/json',
                'tenant' => $user->getAllUserIndex(),
                'X-Proxy-User' => $this->kibanaAdminUsername,
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
                    'template' => $template->getName(),
                    'status_code' => $response->getStatusCode(),
                    'monitoring_set' => $monitoringSet->getName(),
                ]
            );
        }

        return true;
    }

    /**
     *
     * @param Application $application
     * @param boolean $shared
     *
     * @return boolean
     */
    public function createApplicationDashboards(Application $application, bool $shared = false): bool
    {
        $templates = $this->templateManager->getBy(['applicationType.id' => $application->getType()->getId()]);

        foreach ($this->msManager->getAll() as $monitoringSet) {
            $replaceService = strtolower($monitoringSet->getQualifier()) . "-" . $application->getName();
            $filtered = array_filter(
                $templates,
                function (Template $element) use ($monitoringSet) {
                    return $element->getMonitoringSet()->getId() === $monitoringSet->getId() && $element->getType() === Template::DASHBOARDS;
                }
            );

            /** @var Template|bool $template */
            $template = reset($filtered);

            if (($template instanceof Template) === false) {
                $this->logger->alert(
                    "leadwire.kibana.createApplicationDashboards",
                    [
                        'error' => sprintf("Template (%s) for type (%s) not found", Template::DASHBOARDS, $application->getType()->getName()),
                    ]
                );
                throw new \Exception(sprintf("Template (%s) for type (%s) not found", Template::DASHBOARDS, $application->getType()->getName()));
            }

            if ($shared === true) {
                $tenant = $application->getSharedIndex();
            } else {
                $tenant = $application->getApplicationIndex();
            }

            $authorization = $this->jwtHelper->encode($this->kibanaAdminUsername, $this->kibanaAdminUuid);

            $content = str_replace("__replace_token__", $replaceService, $template->getContent());
            $content = str_replace("__replace_service__", $replaceService, $content);

            $headers = [
                'kbn-xsrf' => true,
                'Content-Type' => 'application/json',
                'tenant' => $tenant,
                'X-Proxy-User' => $this->kibanaAdminUsername,
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
                    'monitoring_set' => $monitoringSet->getName(),
                ]
            );
        }

        return true;
    }

    /**
     * @param User $user
     *
     * @return void
     */
    public function loadIndexPatternForAllUser(User $user)
    {
        if ($this->hasAllUserTenant === false) {
            return;
        }

        $defaultType = $this->applicationTypeManager->getOneBy(['name' => ApplicationType::DEFAULT_TYPE]);

        if ($defaultType === null) {
            $this->logger->alert(
                "leadwire.kibana.loadIndexPatternForAllUser",
                [
                    "error" => "Java Application Type not found.\nDid you forget to launch bin/console leadwire:install ?",
                ]
            );

            throw new \Exception("Java Application Type not found.");
        }

        $templates = $this->templateManager->getBy(['applicationType.id' => $defaultType->getId()]);

        foreach ($this->msManager->getAll() as $monitoringSet) {
            $filtered = array_filter(
                $templates,
                function (Template $element) use ($monitoringSet) {
                    return $element->getMonitoringSet()->getId() === $monitoringSet->getId() && $element->getType() === Template::INDEX_PATTERN;
                }
            );

            /** @var Template|bool $template */
            $template = reset($filtered);

            if (($template instanceof Template) === false) {
                $this->logger->alert(
                    "leadwire.kibana.loadIndexPatternForAllUser",
                    [
                        'error' => sprintf("Template (%s) for type (%s) not found", Template::INDEX_PATTERN, $defaultType->getName()),
                    ]
                );
                throw new \Exception(sprintf("Template (%s) for type (%s) not found", Template::INDEX_PATTERN, $defaultType->getName()));
            }

            $content = str_replace("__replace_token__", $user->getAllUserIndex(), $template->getContent());
            $authorization = $this->jwtHelper->encode($this->kibanaAdminUsername, $this->kibanaAdminUuid);
            $headers = [
                'kbn-xsrf' => true,
                'Content-Type' => 'application/json',
                'tenant' => $user->getAllUserIndex(),
                'X-Proxy-User' => $this->kibanaAdminUsername,
                'Authorization' => "Bearer $authorization",
            ];

            $response = $this->httpClient->post(
                $this->url . "api/saved_objects/index-pattern/{$user->getAllUserIndex()}",
                [
                    'headers' => $headers,
                    'body' => $content,
                ]
            );

            $this->logger->notice(
                "leadwire.kibana.loadIndexPatternForAllUser",
                [
                    'url' => $this->url . "api/saved_objects/index-pattern/{$user->getAllUserIndex()}",
                    'verb' => 'POST',
                    'headers' => $headers,
                    'status_code' => $response->getStatusCode(),
                    'monitoring_set' => $monitoringSet->getName(),
                ]
            );

            $this->makeDefaultIndex($user->getAllUserIndex(), $user->getAllUserIndex());
        }
    }

    /**
     * * curl --insecure -H "Authorization: Bearer ${authorization}" -X POST "$protocol://$host:$port/api/saved_objects/index-pattern/$appname" -H 'kbn-xsrf: true' -H 'Content-Type: application/json' -d @/home/centos/pack_curl/apmserver$$.json
     *
     * @param Application $application
     * @param string $tenant
     *
     * @return bool
     */
    public function loadIndexPatternForApplication(Application $application, string $tenant): bool
    {
        $templates = $this->templateManager->getBy(['applicationType.id' => $application->getType()->getId()]);

        foreach ($this->msManager->getAll() as $monitoringSet) {
            $filtered = array_filter(
                $templates,
                function (Template $element) use ($monitoringSet) {
                    return $element->getMonitoringSet()->getId() === $monitoringSet->getId() && $element->getType() === Template::INDEX_PATTERN;
                }
            );

            /** @var Template|bool $template */
            $template = reset($filtered);

            if (($template instanceof Template) === false) {
                $this->logger->alert(
                    "leadwire.kibana.loadIndexPatternForApplication",
                    [
                        'error' => sprintf("Template (%s) not found for type (%s)", Template::INDEX_PATTERN, $application->getType()->getName()),
                    ]
                );
                throw new \Exception(sprintf("Template (%s) not found for type (%s)", Template::INDEX_PATTERN, $application->getType()->getName()));
            }

            $indexPattern = strtolower($monitoringSet->getQualifier()) . "-" . $application->getName();

            $content = str_replace("__replace_token__", $indexPattern, $template->getContent());

            $headers = [
                'kbn-xsrf' => true,
                'Content-Type' => 'application/json',
                'tenant' => $tenant,
                'X-Proxy-User' => $this->kibanaAdminUsername,
            ];

            $authorization = $this->jwtHelper->encode($this->kibanaAdminUsername, $this->kibanaAdminUuid);

            $headers['Authorization'] = "Bearer $authorization";

            $response = $this->httpClient->post(
                $this->url . "api/saved_objects/index-pattern/$indexPattern",
                [
                    'headers' => $headers,
                    'body' => $content,
                ]
            );

            $this->logger->notice(
                "leadwire.kibana.loadIndexPatternForApplication",
                [
                    'url' => $this->url . "api/saved_objects/index-pattern/$indexPattern",
                    'verb' => 'POST',
                    'headers' => $headers,
                    'status_code' => $response->getStatusCode(),
                    'monitoring_set' => $monitoringSet->getName(),
                ]
            );
        }

        return true;
    }

    
     /**
     * * curl --insecure -H "Authorization: Bearer ${authorization}" -X POST "$protocol://$host:$port/api/saved_objects/index-pattern/default" -H 'kbn-xsrf: true' -H 'Content-Type: application/json' -d '{ "attributes": { "title": "*" }}'
     *
     * @param Application $application
     * @param string $tenant
     *
     * @return bool
     */
    public function loadDefaultIndex(string $tenant, string $value): bool
    {
            $indexPattern = $value;

            $content = '{ "attributes": { "title": "*" }}';

            $headers = [
                'kbn-xsrf' => true,
                'Content-Type' => 'application/json',
                'tenant' => $tenant,
                'X-Proxy-User' => $this->kibanaAdminUsername,
            ];

            $authorization = $this->jwtHelper->encode($this->kibanaAdminUsername, $this->kibanaAdminUuid);

            $headers['Authorization'] = "Bearer $authorization";

            $response = $this->httpClient->post(
                $this->url . "api/saved_objects/index-pattern/$indexPattern",
                [
                    'headers' => $headers,
                    'body' => $content,
                ]
            );

            $this->logger->notice(
                "leadwire.kibana.loadDefaultIndex",
                [
                    'url' => $this->url . "api/saved_objects/index-pattern/$indexPattern",
                    'verb' => 'POST',
                    'headers' => $headers,
                    'status_code' => $response->getStatusCode(),
                ]
            );

        return true;
    }

    
    /**
     * @param User $user
     *
     * @return void
     */
    public function loadIndexPatternForUserTenant(User $user)
    {
        $userAccessibleApplications = $this->permissionManager->getAccessibleApplications($user);
        foreach ($userAccessibleApplications as $application) {
            $this->loadIndexPatternForApplication($application, $user->getUserIndex());
        }
    }
    /**
     * * curl --insecure  -H "Authorization: Bearer ${authorization}"  -XGET "https://kibana.leadwire.io/api/saved_objects/index-pattern/${appname}" -H 'kbn-xsrf: true' -H 'Content-Type: application/json'
     *
     * @param string $applicationName
     *
     * @return bool
     */
    public function checkIndexPattern(string $applicationName): bool
    {
        $authorization = $this->jwtHelper->encode($this->kibanaAdminUsername, $this->kibanaAdminUuid);
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
     * @param string $tenant
     * @param string $value
     *
     * @return void
     */
    public function makeDefaultIndex(string $tenant, string $value)
    {
        $authorization = $this->jwtHelper->encode($this->kibanaAdminUsername, $this->kibanaAdminUuid);

        $headers = [
            'kbn-xsrf' => true,
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer $authorization",
            'tenant' => $tenant,
            'X-Proxy-User' => $this->kibanaAdminUsername,
        ];

        $content = json_encode(['value' => $value]);

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
