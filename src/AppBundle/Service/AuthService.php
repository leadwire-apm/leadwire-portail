<?php

namespace AppBundle\Service;

use AppBundle\Document\User;
use AppBundle\Document\AccessLevel;
use AppBundle\Manager\UserManager;
use AppBundle\Service\ElasticSearchService;
use AppBundle\Service\JWTHelper;
use AppBundle\Service\KibanaService;
use AppBundle\Service\LdapService;
use AppBundle\Service\ProcessService;
use AppBundle\Service\EnvironmentService;
use Firebase\JWT\ExpiredException;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AuthService
{
    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var LdapService
     */
    private $ldapService;

    /**
     * @var ApplicationService
     */
    private $applicationService;

    /**
     * @var ElasticSearchService
     */
    private $esService;

    /**
     * @var KibanaService
     */
    private $kibanaService;

    /**
     * @var ProcessService
     */
    private $processService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var JWTHelper
     */
    private $jwtHelper;

    /**
     * @var string
     */
    private $appDomain;

    /**
     * @var array
     */
    private $authProviderSettings;

    /**
     * @var string
     */
    private $superAdminUsername;

    /**
     * @var EnvironmentService
     */
    private $environmentService;

    public function __construct(
        UserManager $userManage,
        ApplicationService $applicationService,
        LdapService $ldapService,
        ElasticSearchService $esService,
        KibanaService $kibanaService,
        ProcessService $processService,
        LoggerInterface $logger,
        JWTHelper $jwtHelper,
        EnvironmentService $environmentService,
        string $appDomain,
        array $authProviderSettings,
        string $superAdminUsername
    ) {
        $this->userManager = $userManage;
        $this->applicationService = $applicationService;
        $this->ldapService = $ldapService;
        $this->esService = $esService;
        $this->kibanaService = $kibanaService;
        $this->processService = $processService;
        $this->jwtHelper = $jwtHelper;
        $this->logger = $logger;
        $this->appDomain = $appDomain;
        $this->authProviderSettings = $authProviderSettings;
        $this->superAdminUsername = $superAdminUsername;
        $this->environmentService = $environmentService;
    }

    /**
     *
     * @param array $params
     * @param string $githubAccessTokenUrl
     * @param string $githubUserAPI
     */
    public function githubProvider(array $params, string $githubAccessTokenUrl, string $githubUserAPI)
    {
        $client = new Client();
        $responseGithub = $client->get($githubAccessTokenUrl . '?' . http_build_query($params))->getBody();

        /* parse the response as array */
        $res = $client->get($githubUserAPI . '?' . $responseGithub, ['headers' => ['User-Agent' => 'leadwire']])->getBody();

        $data = json_decode($res, true);

        $user = $this->userManager->getOneBy(['username' => $data['login']]);

        if ($user === null) {
            $user = $this->handleNewUser($data);
        } else {
            $this->processService->emit($user, "heavy-operations-done", "Succeeded");
            $this->validateActiveStatus($user);
        }

        $this->checkSuperAdminRoles($user);
        $this->processService->emit($user, "heavy-operations-done", "Succeded");

        return $user;
    }

    /**
     * @param array $params
     */
    public function loginProvider(array $params)
    {
        $user = $this->userManager->getOneBy($params);

        if ($user === null) {
            $this->processService->emit($user, "heavy-operations-done", "Failed");
            throw new AccessDeniedHttpException("User is undefined");
        } else {
            $this->processService->emit($user, "heavy-operations-done", "Succeded");
            $this->validateActiveStatus($user);
            $this->checkSuperAdminRoles($user);
        }
        return $user;
    }

    public function proxyLoginProvider(array $params)
    {
        $user = $this->userManager->getOneBy(['username' => $params['username']]);

        if ($user === null) {
            $user = $this->handleNewUser($params);
        } else {
            $this->processService->emit($user, "heavy-operations-done", "Succeded");
            $this->validateActiveStatus($user);
        }

        $this->checkSuperAdminRoles($user);
        return $user;
    }

    /**
     *
     * @param User $user
     *
     * @return string
     */
    public function generateToken(User $user)
    {
        return $this->jwtHelper->encode($user->getUsername(), $user->getUserIndex());
    }

    /**
     *
     * @param string $token
     *
     * @return mixed
     */
    public function decodeToken($token)
    {
        $decoded = $this->jwtHelper->decode($token, $this->authProviderSettings['settings']['token_secret']);

        if (isset($decoded->host) === false || $decoded->host !== $this->appDomain) {
            throw new ExpiredException('Invalide token');
        }

        return $decoded;
    }

    /**
     * @param string $authorization
     * @return User|null
     */
    public function getUserFromToken($authorization)
    {
        $jwt = explode(' ', $authorization);
        $token = $this->decodeToken($jwt[1]);

        return $this->userManager->getOneBy(['uuid' => str_replace("user_", "", $token->user)]);
    }

    /**
     *
     * @param array $userData
     *
     * @return User|null
     */
    protected function addUserWithEmail(array $userData): ?User
    {
        try {
            if (strtolower($userData['group']) === 'user') {
                $role = [User::DEFAULT_ROLE];
            } else if (strtolower($userData['group']) === 'admin') {
                $role = [User::ROLE_SUPER_ADMIN];
            } else {
                $role = []; // Should never happen
            }

            $user = $this->userManager->createWithEmail(
                $userData['username'],
                $userData['username'],
                'https://img.icons8.com/metro/26/000000/administrator-male.png',
                $userData['username'], //name
                $role,
                true,
                $userData['email']
            );

            return $user;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());

            return null;
        }
    }

    /**
     *
     * @param array $userData
     *
     * @return User
     */
    protected function addUser(array $userData): User
    {
        $uuid1 = Uuid::uuid1();
        $user = $this->userManager->create(
            $userData['login'],
            $uuid1->toString(),
            $userData['avatar_url'],
            $userData['name'],
            [User::DEFAULT_ROLE],
            true
        );

        return $user;
    }

    /**
     * Makes sure that the user with the configured super admin username has the right access role
     *
     * @param User $user
     *
     * @return void
     */
    private function checkSuperAdminRoles(User $user): void
    {
        if ($user->getUsername() === $this->superAdminUsername) {
            $user->revoke(User::ROLE_SUPER_ADMIN);
            $user->promote(User::ROLE_SUPER_ADMIN);
        }

        $this->userManager->update($user);
    }

    private function handleNewUser(array $parameters): ?User
    {
        // We're dealing with a new user
        $user = null;

        if (array_key_exists('group', $parameters) === true) {
            $user = $this->addUserWithEmail($parameters);
        } else {
            $user = $this->addUser($parameters);
        }

        if ($user !== null) {
            //create user in opendistro
            $this->esService->createUser($user);
            $this->esService->updateRoleMapping("add", "staging", $user, "demo", false);
            $this->esService->createTenant($user->getUsername());
            
            if($user->hasRole('ROLE_SUPER_ADMIN') || $user->hasRole('ROLE_ADMIN')) {
                $this->esService->updateRoleMapping("add", "staging", $user, "demo", true);
            }
            
            $this->processService->emit($user, "heavy-operations-in-progress", "Register Applications");
            $this->applicationService->registerDemoApplications($user);

            $this->processService->emit($user, "heavy-operations-in-progress", "Creating Kibana Dashboards");
            $this->kibanaService->loadIndexPatternForUserTenant($user);

            $this->kibanaService->loadDefaultIndex($user->getUserIndex(), 'default');
            $this->kibanaService->makeDefaultIndex($user->getUserIndex(), 'default');

            $this->processService->emit($user, "heavy-operations-done", "Succeded");
        }

        return $user;
    }

    private function validateActiveStatus(User $user)
    {
        // Check if user has been deleted
        if ($user->isDeleted() === true) {
            $this->logger->notice("leadwire.auth.githubProvider", ["event" => "Deleted user {$user->getUsername()} tried to login"]);
            throw new AccessDeniedHttpException("User is deleted");
        }

        // Check if user is locked
        if ($user->isLocked() === true) {
            $this->logger->notice("leadwire.auth.githubProvider", ["event" => "Locked user {$user->getUsername()} tried to login"]);
            throw new AccessDeniedHttpException($user->getLockMessage());
        }
    }
}
