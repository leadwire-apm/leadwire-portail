<?php

namespace AppBundle\Service;

use AppBundle\Document\User;
use AppBundle\Manager\UserManager;
use AppBundle\Service\ElasticSearchService;
use AppBundle\Service\JWTHelper;
use AppBundle\Service\KibanaService;
use AppBundle\Service\LdapService;
use Firebase\JWT\ExpiredException;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use \Firebase\JWT\JWT;

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
     * @var SearchGuardService
     */
    private $sgService;

    public function __construct(
        UserManager $userManage,
        ApplicationService $applicationService,
        LdapService $ldapService,
        ElasticSearchService $esService,
        KibanaService $kibanaService,
        LoggerInterface $logger,
        JWTHelper $jwtHelper,
        SearchGuardService $sgService,
        string $appDomain,
        array $authProviderSettings,
        string $superAdminUsername
    ) {
        $this->userManager = $userManage;
        $this->applicationService = $applicationService;
        $this->ldapService = $ldapService;
        $this->esService = $esService;
        $this->kibanaService = $kibanaService;
        $this->jwtHelper = $jwtHelper;
        $this->logger = $logger;
        $this->appDomain = $appDomain;
        $this->authProviderSettings = $authProviderSettings;
        $this->superAdminUsername = $superAdminUsername;
        $this->sgService = $sgService;
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
            // We're dealing with a new user
            $user = $this->addUser($data);
            $this->logger->notice("leadwire.auth.githubProvider", ["event" => "Created new user {$user->getUsername()}"]);

            if ($user !== null) {
                // User creation in DB is successful
                // Should create LDAP & ElasticSearch entries
                $this->ldapService->createNewUserEntries($user);
                $this->ldapService->registerDemoApplications($user);
                $this->applicationService->registerDemoApplications($user);

                $this->esService->deleteIndex("user_" . $user->getUuid());
                $this->kibanaService->loadIndexPatternForDemoApplications($user);

                $this->esService->deleteIndex("all_user_" . $user->getUuid());
                $this->kibanaService->loadIndexPatternForAllUser($user);
                $this->kibanaService->createAllUserDashboard($user);

                $this->sgService->updateSearchGuardConfig();
            }
        } else {
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
        $token = [
            'host' => $this->appDomain,
            'user' => $user->getIndex(),
            'name' => $user->getUsername(),
            'iat' => time(),
            'exp' => time() + 1800 + 1800 * 2,
            'nbf' => time(),
        ];

        return $this->jwtHelper->encode($user->getUsername(), $user->getIndex());
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
     * Makes sure that the user with the configured super admin username has the right access role
     *
     * @param User $user
     *
     * @return void
     */
    private function checkSuperAdminRoles(User $user): void
    {
        if ($user->getUsername() === $this->superAdminUsername &&
            $user->hasRole(User::ROLE_SUPER_ADMIN) === false
        ) {
            $user->promote(User::ROLE_SUPER_ADMIN);
            $this->userManager->update($user);
        }
    }
}
