<?php

namespace AppBundle\Service;


use ATS\UserBundle\Document\User;
use ATS\UserBundle\Manager\UserManager;
use Firebase\JWT\ExpiredException;
use \Firebase\JWT\JWT;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AuthService
{
    private $container;
    private $userManager;

    public function __construct(ContainerInterface $container, UserManager $userManager)
    {
        $this->container = $container;
        $this->userManager = $userManager;
    }

    private function get ($name)
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
            $res = $client->request('GET', $githubUserAPI. '?' . $responseGithub, [
                'headers' => [ 'User-Agent' => 'leadwire']])->getBody();


            $data = json_decode($res, true);
            $user = $this->checkAndAdd($data);
            $data['_id'] = $user->getId();
            return $data;

        } catch (GuzzleException $e) {
            sd( $e->getMessage());
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
       $token = JWT::decode($jwt, $this->get('token_secret'), ['HS256']);

       if (!isset($token->host) || $token->host!= $this->get('app_domain'))
           throw new ExpiredException('Invalide token');

       return $token;
    }

    protected function checkAndAdd(array $userData)
    {
        $dbUser = $this->userManager->getOneBy(['username' => $userData['login']]);

        if (!$dbUser)
        {
            $this->userManager->create($userData['login'], "", [User::DEFAULT_ROLE], true);
            return $this->userManager->getUserByUsername($userData['login']);
        } else
            return $dbUser;
    }
}