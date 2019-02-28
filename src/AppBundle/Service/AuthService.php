<?php

namespace AppBundle\Service;

use AppBundle\Document\User;
use AppBundle\Manager\UserManager;
use AppBundle\Service\ElasticSearchService;
use AppBundle\Service\LdapService;
use Firebase\JWT\ExpiredException;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Config\Definition\Exception\Exception;
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
     * @var LoggerInterface
     */
    private $logger;

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

    public function __construct(
        UserManager $userManage,
        ApplicationService $applicationService,
        LdapService $ldapService,
        ElasticSearchService $esService,
        LoggerInterface $logger,
        string $appDomain,
        array $authProviderSettings,
        string $superAdminUsername
    ) {
        $this->userManager = $userManage;
        $this->applicationService = $applicationService;
        $this->ldapService = $ldapService;
        $this->esService = $esService;
        $this->logger = $logger;
        $this->appDomain = $appDomain;
        $this->authProviderSettings = $authProviderSettings;
        $this->superAdminUsername = $superAdminUsername;
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
        $responseGithub = $client->request(
            'GET',
            $githubAccessTokenUrl . '?' . http_build_query($params)
        )->getBody();

        /* parse the response as array */
        $res = $client->request(
            'GET',
            $githubUserAPI . '?' . $responseGithub,
            [
                'headers' => ['User-Agent' => 'leadwire']]
        )->getBody();

        $data = json_decode($res, true);

        $user = $this->userManager->getOneBy(['username' => $data['login']]);

        if ($user === null) {
            // We're dealing with a new user
            $user = $this->addUser($data);

            if ($user !== null) {
                // User creation in DB is successful
                // Should create LDAP & ElasticSearch entries
                $this->ldapService->createNewUserEntries($user);
                $this->ldapService->registerDemoApplications($user);
                $this->applicationService->registerDemoApplications($user);
                $this->esService->resetUserIndexes($user);
            }
        } else {
            // Check if user has been deleted
            if ($user->isDeleted() === true) {
                throw new AccessDeniedHttpException("User is deleted");
            }

            // Check if user is locked
            if ($user->isLocked() === true) {
                throw new AccessDeniedHttpException($user->getLockMessage());
            }
        }

        $this->checkSuperAdminRoles($user);

        return $user;
    }

    /**
     * @param array $params
     */
    public function loginProvider(array $params)
    {
        $user = $this->userManager->getOneBy($params);

        if ($user === null) {
          throw new AccessDeniedHttpException("User is undefined");
        } else {
            // Check if user has been deleted
            if ($user->isDeleted() === true) {
                throw new AccessDeniedHttpException("User is deleted");
            }

            // Check if user is locked
            if ($user->isLocked() === true) {
                throw new AccessDeniedHttpException($user->getLockMessage());
            }

            $this->checkSuperAdminRoles($user);
        }
        return $user;
    }
    
    function proxyLoginProvider(array $params){

        $user = $this->userManager->getOneBy($params);

        if ($user === null) {
            // We're dealing with a new user
            $user = $this->addUserWithEmail($params);

            if ($user !== null) {
                // User creation in DB is successful
                // Should create LDAP & ElasticSearch entries
                $this->ldapService->createNewUserEntries($user);
                $this->ldapService->registerDemoApplications($user);
                $this->applicationService->registerDemoApplications($user);
                $this->esService->resetUserIndexes($user);
            }
        } else {
            // Check if user has been deleted
            if ($user->isDeleted() === true) {
                throw new AccessDeniedHttpException("User is deleted");
            }

            // Check if user is locked
            if ($user->isLocked() === true) {
                throw new AccessDeniedHttpException($user->getLockMessage());
            }
        }

        $this->checkSuperAdminRoles($user);

        return $user;
    }

    /**
     *
     * @param User $user
     * @param string $tokenSecret
     *
     * @return string
     */
    public function generateToken(User $user, $tokenSecret)
    {
        $token = [
            'host' => $this->appDomain,
            'user' => $user->getIndex(),
            'name' => $user->getUsername(),
            'iat' => time(),
            'exp' => time() + 1800 + 1800 * 2,
            'nbf' => time(),
        ];

        return JWT::encode($token, $tokenSecret);
    }

    /**
     *
     * @param string $jwt
     *
     * @return mixed
     */
    public function decodeToken($jwt)
    {
        $token = JWT::decode($jwt, $this->authProviderSettings['settings']['token_secret'], ['HS256']);

        if (isset($token->host) === false || $token->host !== $this->appDomain) {
            throw new ExpiredException('Invalide token');
        }

        return $token;
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
            $uuid1 = Uuid::uuid1();
            $user = $this->userManager->createWithEmail(
                $userData['username'],
                $uuid1->toString(),
                'https://www.pngarts.com/files/3/Avatar-PNG-Image.png',
                $userData['username'],//name
                [User::DEFAULT_ROLE],
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
     * @return User|null
     */
    protected function addUser(array $userData): ?User
    {
        try {
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
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());

            return null;
        }
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
