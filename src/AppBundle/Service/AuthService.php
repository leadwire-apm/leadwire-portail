<?php

namespace AppBundle\Service;

use AppBundle\Document\User;
use AppBundle\Manager\UserManager;
use Ramsey\Uuid\Uuid;
use Firebase\JWT\ExpiredException;
use \Firebase\JWT\JWT;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Ldap;

class AuthService
{
    private $container;
    private $userManager;

    public function __construct(ContainerInterface $container, UserManager $userManager)
    {
        $this->container = $container;
        $this->userManager = $userManager;
    }

    private function get($name)
    {
        return $this->container->getParameter($name);
    }

    public function githubProvider(array $params, string $githubAccessTokenUrl, string $githubUserAPI)
    {
        $client = new \GuzzleHttp\Client();
        try {
            $responseGithub = $client->request('GET', $githubAccessTokenUrl . '?' . http_build_query($params))->getBody();
            // parse_str($responseGithub, $responseGithub);
            /* parse the response as array */
            $res = $client->request(
                'GET',
                $githubUserAPI. '?' . $responseGithub,
                [
                    'headers' => [ 'User-Agent' => 'leadwire']]
            )->getBody();


            $data = json_decode($res, true);
            $user = $this->checkAndAdd($data);
            $data['_id'] = $user->getId();
            if (!$user->getEmail()) {
                $this->createLdapEntry($user->getUsername());
            }
            return $data;
        } catch (GuzzleException $e) {
            sd($e->getMessage());
        } catch (\Exception $e) {
            sd($e);
        }
    }

    public function generateToken($userId, $tokenSecret)
    {
        $token = [
            'host' => $this->get('app_domain'),
            'user'=>  $userId,
            'name'=>  "leadwire-apm-test",
            'iat'=> time(),
            'exp'=>  time() + 1800,
            'nbf'=> time()
        ];

        return JWT::encode($token, $tokenSecret);
        //
    }

    public function decodeToken($jwt)
    {
        $token = JWT::decode($jwt, $this->get('auth_providers')['settings']['token_secret'], ['HS256']);

        if (!isset($token->host) || $token->host!= $this->get('app_domain')) {
            throw new ExpiredException('Invalide token');
        }

        return $token;
    }

    protected function checkAndAdd(array $userData)
    {
        try {
            $dbUser = $this->userManager->getOneBy(['username' => $userData['login']]);

            if (!$dbUser) {
                $uuid1 = Uuid::uuid1();
                $this->userManager->create($userData['login'], $uuid1->toString(), $userData['avatar_url'], [User::DEFAULT_ROLE], true);
                $dbUser = $this->userManager->getUserByUsername($userData['login']);
                return $dbUser;
            } else {
                return $dbUser;
            }
        } catch (UnsatisfiedDependencyException $e) {
            throw new Exception('Caught exception: ' . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected function createLdapEntry($username)
    {

        $ldapParameters = $this->get('ldap');

        $ldap = Ldap::create('ext_ldap', [
            'connection_string' => 'ldap://' . $ldapParameters['host'] . ':' . $ldapParameters['port'],
        ]);

        $ldap->bind($ldapParameters['dn_user'], $ldapParameters['mdp']);
        $entry = new Entry(
            "cn=application_name,ou=Group,dc=leadwire,dc=io",
            [

                'cn' => 'application_name',
                'objectClass' => ['groupofnames'],
                'member' => "cn=$username,ou=People,dc=leadwire,dc=io"
            ]
        );

        $entryManager = $ldap->getEntryManager();

        $entryManager->add($entry);
    }
}
