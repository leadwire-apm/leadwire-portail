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

    public function __construct(
        UserManager $userManage,
        LdapService $ldapService,
        ElasticSearchService $esService,
        LoggerInterface $logger,
        string $appDomain,
        array $authProviderSettings
    ) {
        $this->userManager = $userManage;
        $this->ldapService = $ldapService;
        $this->esService = $esService;
        $this->logger = $logger;
        $this->appDomain = $appDomain;
        $this->authProviderSettings = $authProviderSettings;
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
            $user = $this->addUser($data);

            if ($user !== false) {
                // New user has been created.
                // Should create LDAP & ElasticSearch entries
                $this->ldapService->createNewUser($user->getUuid());
                $this->ldapService->registerDemoApplications($user->getUuid());
                $this->esService->resetUserIndexes($user);
                $this->esService->createDefaultApplications($user);
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

    protected function addUser(array $userData)
    {
        try {
            $uuid1 = Uuid::uuid1();
            $this->userManager->create(
                $userData['login'],
                $uuid1->toString(),
                $userData['avatar_url'],
                $userData['name'],
                [User::DEFAULT_ROLE],
                true
            );
            $dbUser = $this->userManager->getUserByUsername($userData['login']);

            return $dbUser;
        } catch (\Exception $e) {
            $this->logger->critical("Exception on User creation ", ['exception' => $e]);

            return false;
        }
    }

    /**
     * @param string $authorization
     * @return array|User
     */
    public function getUserFromToken($authorization)
    {
        $jwt = explode(' ', $authorization);
        $token = $this->decodeToken($jwt[1]);

        return $this->userManager->getOneBy(['uuid' => str_replace("user_", "", $token->user)]);
    }
}
