<?php

namespace AppBundle\Service;

use AppBundle\Document\User;
use AppBundle\Manager\UserManager;
use AppBundle\Service\ElasticSearch;
use AppBundle\Service\LdapService;
use Firebase\JWT\ExpiredException;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \Firebase\JWT\JWT;

class AuthService
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var LdapService
     */
    private $ldapService;

    /**
     * @var ElasticSearch
     */
    private $elastic;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ContainerInterface $container,
        UserManager $userManage,
        LdapService $ldapService,
        ElasticSearch $elastic,
        LoggerInterface $logger
    ) {
        $this->container = $container;
        $this->userManager = $userManage;
        $this->ldapService = $ldapService;
        $this->elastic = $elastic;
        $this->logger = $logger;
    }

    private function get($name)
    {
        return $this->container->getParameter($name);
    }

    public function githubProvider(array $params, string $githubAccessTokenUrl, string $githubUserAPI)
    {
        $client = new \GuzzleHttp\Client();
        try {
            $responseGithub = $client->request(
                'GET',
                $githubAccessTokenUrl . '?' . http_build_query($params)
            )->getBody();
            // parse_str($responseGithub, $responseGithub);
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
                    $this->ldapService->createUserEntry($user->getUuid());
                    $this->elastic->resetUserIndexes($user);
                }
            }

            return $user;
        } catch (\Exception $e) {
            $this->logger->critical("Exception on User creation ", ['exception' => $e]);
            return false;
        }
    }

    public function generateToken(User $user, $tokenSecret)
    {
        $token = [
            'host' => $this->get('app_domain'),
            'user' => $user->getIndex(),
            'name' => $user->getUsername(),
            'iat' => time(),
            'exp' => time() + 1800 + 1800 * 2,
            'nbf' => time(),
        ];

        return JWT::encode($token, $tokenSecret);
    }

    public function decodeToken($jwt)
    {
        $token = JWT::decode($jwt, $this->get('auth_providers')['settings']['token_secret'], ['HS256']);

        if (isset($token->host) === false || $token->host !== $this->get('app_domain')) {
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
