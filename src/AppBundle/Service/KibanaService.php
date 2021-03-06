<?php
namespace AppBundle\Service;

use AppBundle\Document\Application;
use AppBundle\Document\Watcher;
use AppBundle\Document\MonitoringSet;
use AppBundle\Document\Template;
use AppBundle\Document\User;
use AppBundle\Manager\ApplicationPermissionManager;
use AppBundle\Service\JWTHelper;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Exception\NotImplementedException;

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
     * @var ApplicationPermissionManager
     */
    private $permissionManager;

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
     * @param ApplicationPermissionManager $permissionManager,
     * @param JWTHelper $jwtHelper
     * @param array $settings
     */
    public function __construct(
        LoggerInterface $logger,
        ApplicationPermissionManager $permissionManager,
        JWTHelper $jwtHelper,
        array $settings = []
    ) {
        $this->logger = $logger;
        $this->permissionManager = $permissionManager;
        $this->jwtHelper = $jwtHelper;
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

        $this->es_password = $settings['kibana_password'];
        $this->email = $settings['kibana_mailer_user'];
    }

    /**
     *
     * @param Application $application
     * @param boolean $shared
     *
     * @return boolean
     */
    public function createApplicationDashboards(Application $application, string $environmentName,bool $shared = false): bool
    {
        /** @var MonitoringSet $monitoringSet */
        foreach ($application->getType()->getMonitoringSets() as $monitoringSet) {
            if ($monitoringSet->isValid() === false) {
                $this->logger->warning(
                    "leadwire.kibana.createApplicationDashboards",
                    [
                        'event' => 'Ignoring invalid MonitoringSet',
                        'monitoring_set' => $monitoringSet->getName(),
                    ]
                );
                continue;
            }
            $replaceService = strtolower($monitoringSet->getQualifier()) . "-*-" . $environmentName . "-" . $application->getName(). "-*";

            /** @var ?Template $template */
            $template = $monitoringSet->getTemplateByType(Template::DASHBOARDS);

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
                $tenant = $environmentName ."-". $application->getSharedIndex();
            } else {
                $tenant = $environmentName ."-". $application->getApplicationIndex();
            }

            $authorization = $this->jwtHelper->encode($this->kibanaAdminUsername, $this->kibanaAdminUuid);

            $content = str_replace("__replace_token__", $replaceService, $template->getContent());
            $content = str_replace("__replace_service__", $application->getName(), $content);

            $headers = [
                'kbn-xsrf' => true,
                'Content-Type' => 'application/json',
                'security_tenant' => $tenant,
                'x-proxy-roles' => $this->kibanaAdminUsername,
                'X-Proxy-User' => $this->kibanaAdminUsername,
                'Authorization' => "Bearer $authorization",
                'x-forwarded-for' => '127.0.0.1'
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
                    'status_text' => $response->getReasonPhrase(),
                ]
            );
        }

        return true;
    }

    /**
     * * curl --insecure -H "Authorization: Bearer ${authorization}" -X POST "$protocol://$host:$port/api/saved_objects/index-pattern/$appname" -H 'kbn-xsrf: true' -H 'Content-Type: application/json' -d @/home/centos/pack_curl/apmserver$$.json
     *
     * @param Application $application
     * @param string $tenant
     *
     * @return bool
     */
    public function loadIndexPatternForApplication(Application $application, string $tenant, string $environmentName): bool
    {
        foreach ($application->getType()->getMonitoringSets() as $monitoringSet) {
            if ($monitoringSet->isValid() === false) {
                $this->logger->warning(
                    "leadwire.kibana.createApplicationDashboards",
                    [
                        'event' => 'Ignoring invalid MonitoringSet',
                        'monitoring_set' => $monitoringSet->getName(),
                    ]
                );
                continue;
            }
            /** @var ?Template $template */
            $template = $monitoringSet->getTemplateByType(Template::INDEX_PATTERN);

            if (($template instanceof Template) === false) {
                $this->logger->alert(
                    "leadwire.kibana.loadIndexPatternForApplication",
                    [
                        'error' => sprintf("Template (%s) not found for type (%s)", Template::INDEX_PATTERN, $application->getType()->getName()),
                    ]
                );
                throw new \Exception(sprintf("Template (%s) not found for type (%s)", Template::INDEX_PATTERN, $application->getType()->getName()));
            }

            $indexPattern = strtolower($monitoringSet->getQualifier()) . "-*-" . $environmentName . "-" . $application->getName(). "-*";

            $content = str_replace("__replace_token__", $indexPattern, $template->getContent());

            $headers = [
                'kbn-xsrf' => true,
                'Content-Type' => 'application/json',
                'security_tenant' => $tenant,
                'x-proxy-roles' => $this->kibanaAdminUsername,
                'X-Proxy-User' => $this->kibanaAdminUsername,
                'x-forwarded-for' => '127.0.0.1',
            ];

            $authorization = $this->jwtHelper->encode($this->kibanaAdminUsername, $this->kibanaAdminUuid);

            $headers['Authorization'] = "Bearer $authorization";

            $response = $this->httpClient->post(
                $this->url . "api/saved_objects/index-pattern/$indexPattern?overwrite=true",
                [
                    'headers' => $headers,
                    'body' => $content,
                ]
            );

            $this->logger->notice(
                "leadwire.kibana.loadIndexPatternForApplication",
                [
                    'url' => $this->url . "api/saved_objects/index-pattern/$indexPattern?overwrite=true",
                    'verb' => 'POST',
                    'headers' => $headers,
                    'status_code' => $response->getStatusCode(),
                    'monitoring_set' => $monitoringSet->getName(),
                    'status_text' => $response->getReasonPhrase(),
                ]
            );
        }

        return true;
    }

    /**
     * * curl --insecure -H "Authorization: Bearer ${authorization}" -X POST "$protocol://$host:$port/api/saved_objects/index-pattern/default" -H 'kbn-xsrf: true' -H 'Content-Type: application/json' -d '{ "attributes": { "title": "*" }}'
     *
     * @param string $tenant
     * @param string $value
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
            'security_tenant' => $tenant,
            'x-proxy-roles' => $this->kibanaAdminUsername,
            'X-Proxy-User' => $this->kibanaAdminUsername,
            'x-forwarded-for' => '127.0.0.1',
        ];

        $authorization = $this->jwtHelper->encode($this->kibanaAdminUsername, $this->kibanaAdminUuid);

        $headers['Authorization'] = "Bearer $authorization";

        $response = $this->httpClient->post(
            $this->url . "api/saved_objects/index-pattern/$indexPattern?overwrite=true",
            [
                'headers' => $headers,
                'body' => $content,
            ]
        );

        $this->logger->notice(
            "leadwire.kibana.loadDefaultIndex",
            [
                'url' => $this->url . "api/saved_objects/index-pattern/$indexPattern?overwrite=true",
                'verb' => 'POST',
                'headers' => $headers,
                'status_code' => $response->getStatusCode(),
                'status_text' => $response->getReasonPhrase(),
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
            foreach($application->getEnvironments as $environment){
                $this->loadIndexPatternForApplication($application, $user->getUserIndex(), $environment->getName());
            }
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
                'status_text' => $response->getReasonPhrase(),
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
            'security_tenant' => $tenant,
            'x-proxy-roles' => $this->kibanaAdminUsername,
            'X-Proxy-User' => $this->kibanaAdminUsername,
            'x-forwarded-for' => '127.0.0.1',
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
                'status_text' => $response->getReasonPhrase(),
            ]
        );
    }

    /**
    *
     * @param string $id
     * @param string $index
     *
     * @return bool
     */
    public function deleteReport(string $id, string $ind) {
        $authorization = $this->jwtHelper->encode($this->kibanaAdminUsername, $this->kibanaAdminUuid);
        $headers = [
            'kbn-xsrf' => true,
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer $authorization",
        ];

        $response = $this->httpClient->delete(
            $this->url . "api/sentinl/report/$id/$ind",
            [
                'headers' => $headers,
            ]
        );

        $this->logger->notice(
            "leadwire.kibana.deleteReport",
            [
                'url' => $this->url . "api/sentinl/report/$id/$index",
                'verb' => 'DELETE',
                'headers' => $headers,
                'status_code' => $response->getStatusCode(),
                'status_text' => $response->getReasonPhrase(),
            ]
        );

        return $response->getStatusCode() === Response::HTTP_OK;
    }

    public function createWatcher(Watcher $watcher, string $tenant) {
        $authorization = $this->jwtHelper->encode($this->kibanaAdminUsername, $this->kibanaAdminUuid);
       
        $content = json_encode([
            "attributes" => [
                "title" => $watcher->getTitle(),
                "disable" => false,
                "report" => true,
                "save_payload" => false,
                "impersonate" => false,
                "spy" =>false,
                "trigger" => [
                    "schedule" => [
                        "later" => $watcher->getShedule()
                    ]
                ],
                "input" => [
                    "search" => [
                        "request" => [
                            "index" => array()                        ]
                    ]
                ],
                "condition" =>  array(),
                "actions" => [
                    "report_admin" => [
                        "report" => [
                            "to" => $watcher->getTo(),
                            "from" => $this->email,
                            "subject" => $watcher->getSubject(),
                            "priority" =>"high",
                            "body" => $watcher->getBody(),
                            "auth" => [
                                "active" => true,
                                "mode" => "basic",
                                "username" => "admin",
                                "password" => $this->es_password
                            ],
                            "snapshot" => [
                                "res" => $watcher->getRes(),
                                "url" => $watcher->getUrl(),
                                "params" => [
                                    "delay" => $watcher->getDelay()
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $headers = [
            'kbn-xsrf' => true,
            'Content-Type' => 'application/json',
            'x-proxy-roles' => $this->kibanaAdminUsername,
            'X-Proxy-User' => $this->kibanaAdminUsername,
            'Authorization' => "Bearer $authorization",
            'x-forwarded-for' => '127.0.0.1',
            'security_tenant' => $tenant
        ];

        $response = $this->httpClient->put(
            $this->url . "api/sentinl/watcher",
            [
                'headers' => $headers,
                'body' => $content,
            ]
        );

        $res = \json_decode($response->getBody());

        $this->logger->notice(
            "leadwire.kibana.createWatcher",
            [
                'url' => $this->url . "api/sentinl/watcher",
                'verb' => 'PUT',
                'headers' => $headers,
                'status_code' => $response->getStatusCode(),
                'status_text' => $response->getReasonPhrase(),
                'pass' => $this->es_password,
                'email' => $this->email
            ]
        );

        if($response->getStatusCode() === Response::HTTP_OK){
            return $res->id;
        } else {
            return null;
        }
    }

    public function executeWatcher(Watcher $watcher, string $tenant) {
        $authorization = $this->jwtHelper->encode($this->kibanaAdminUsername, $this->kibanaAdminUuid);

           $content = json_encode([
            "attributes" => [
                "id" => $watcher->getKibanaId(),
                "title" => $watcher->getTitle(),
                "disable" => !$watcher->isEnabled(),
                "report" => true,
                "save_payload" => false,
                "impersonate" => false,
                "spy" =>false,
                "trigger" => [
                    "schedule" => [
                        "later" => $watcher->getShedule()
                    ]
                ],
                "input" => [
                    "search" => [
                        "request" => [
                            "index" => array()                        ]
                    ]
                ],
                "condition" =>  array(),
                "actions" => [
                    "report_admin" => [
                        "report" => [
                            "to" => $watcher->getTo(),
                            "from" => $this->email,
                            "subject" => $watcher->getSubject(),
                            "priority" =>"high",
                            "body" => $watcher->getBody(),
                            "auth" => [
                                "active" => true,
                                "mode" => "basic",
                                "username" => "admin",
                                "password" => $this->es_password
                            ],
                            "snapshot" => [
                                "res" => $watcher->getRes(),
                                "url" => $watcher->getUrl(),
                                "params" => [
                                    "delay" => $watcher->getDelay()
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $headers = [
            'kbn-xsrf' => true,
            'Content-Type' => 'application/json',
            'x-proxy-roles' => $this->kibanaAdminUsername,
            'X-Proxy-User' => $this->kibanaAdminUsername,
            'Authorization' => "Bearer $authorization",
            'x-forwarded-for' => '127.0.0.1',
            'security_tenant' => $tenant,
        ];

        $response = $this->httpClient->post(
            $this->url . "api/sentinl/watcher/_execute",
            [
                'headers' => $headers,
                'body' => $content,
            ]
        );

        $this->logger->notice(
            "leadwire.kibana.executeWatcher",
            [
                'url' => $this->url . "api/sentinl/watcher/_execute",
                'verb' => 'POST',
                'headers' => $headers,
                'status_code' => $response->getStatusCode(),
                'status_text' => $response->getReasonPhrase(),
            ]
        );

        return true;
    }

    public function deleteWatcher(Watcher $watcher, string $tenant) {
        $authorization = $this->jwtHelper->encode($this->kibanaAdminUsername, $this->kibanaAdminUuid);
       

        $headers = [
            'kbn-xsrf' => true,
            'Content-Type' => 'application/json',
            'x-proxy-roles' => $this->kibanaAdminUsername,
            'X-Proxy-User' => $this->kibanaAdminUsername,
            'Authorization' => "Bearer $authorization",
            'x-forwarded-for' => '127.0.0.1',
            'security_tenant' => $tenant
        ];

        $response = $this->httpClient->delete(
            $this->url . "api/sentinl/watcher/" . $watcher->getKibanaId(),
            [
                'headers' => $headers
            ]
        );

        $this->logger->notice(
            "leadwire.kibana.deleteWatcher",
            [
                'url' => $this->url . "api/sentinl/watcher/" . $watcher->getKibanaId(),
                'verb' => 'DELETE',
                'headers' => $headers,
                'status_code' => $response->getStatusCode(),
                'status_text' => $response->getReasonPhrase(),
            ]
        );

        return $response->getStatusCode() === Response::HTTP_OK;
    }

    public function handelWatcher(Watcher $watcher, string $tenant) {
        $authorization = $this->jwtHelper->encode($this->kibanaAdminUsername, $this->kibanaAdminUuid);
       
        $content = json_encode([
            "attributes" => [
                "title" => $watcher->getTitle(),
                "disable" => !$watcher->isEnabled(),
                "report" => true,
                "save_payload" => false,
                "impersonate" => false,
                "spy" =>false,
                "trigger" => [
                    "schedule" => [
                        "later" => $watcher->getShedule()
                    ]
                ],
                "input" => [
                    "search" => [
                        "request" => [
                            "index" => array()                        ]
                    ]
                ],
                "condition" =>  array(),
                "actions" => [
                    "report_admin" => [
                        "report" => [
                            "to" => $watcher->getTo(),
                            "from" => $this->email,
                            "subject" => $watcher->getSubject(),
                            "priority" =>"high",
                            "body" => $watcher->getBody(),
                            "auth" => [
                                "active" => true,
                                "mode" => "basic",
                                "username" => "admin",
                                "password" => $this->es_password
                            ],
                            "snapshot" => [
                                "res" => $watcher->getRes(),
                                "url" => $watcher->getUrl(),
                                "params" => [
                                    "delay" => $watcher->getDelay()
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $headers = [
            'kbn-xsrf' => true,
            'Content-Type' => 'application/json',
            'x-proxy-roles' => $this->kibanaAdminUsername,
            'X-Proxy-User' => $this->kibanaAdminUsername,
            'Authorization' => "Bearer $authorization",
            'x-forwarded-for' => '127.0.0.1',
            'security_tenant' => $tenant
        ];

        $response = $this->httpClient->put(
            $this->url . "api/sentinl/watcher/" . $watcher->getKibanaId(),
            [
                'headers' => $headers,
                'body' => $content,
            ]
        );

        $this->logger->notice(
            "leadwire.kibana.handelWatcher",
            [
                'url' => $this->url . "api/sentinl/watcher/" . $watcher->getKibanaId(),
                'verb' => 'PUT',
                'headers' => $headers,
                'status_code' => $response->getStatusCode(),
                'status_text' => $response->getReasonPhrase(),
            ]
        );

        return $response->getStatusCode() === Response::HTTP_OK;
    }

}
