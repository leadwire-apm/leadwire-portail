<?php

namespace ATS\UserBundle\Service;

use ATS\UserBundle\Manager\UserManager;
use FOS\OAuthServerBundle\Model\AccessTokenManagerInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;

class AuthService
{

    /**
     * @var UserManager
     */

    private $userManager;

    /**
     * @var ClientManagerInterface
     */

    private $clientManager;

    /**
     * @var AccessTokenManagerInterface
     */

    private $accessTokenManager;

    /**
     * @param UserManager $userManager
     * @param ClientManagerInterface $clientManager
     * @param AccessTokenManagerInterface $accessTokenManager
     */

    public function __construct(
        UserManager $userManager,
        ClientManagerInterface $clientManager,
        AccessTokenManagerInterface $accessTokenManager
    ) {
        $this->userManager = $userManager;
        $this->clientManager = $clientManager;
        $this->accessTokenManager = $accessTokenManager;
    }

    /**
     * @param string $json
     */

    public function register($json)
    {
        $rawUser = json_decode($json);

        $this
            ->userManager
            ->create($rawUser->username, $rawUser->password)
        ;
    }

    /**
     * @param string $username
     * @param string $clientId
     * @return string|null
     */

    public function loginCheck($username, $clientId)
    {
        $user = $this
            ->userManager
            ->getUserByUsername($username)
        ;

        if ($user) {
            return $this->getClientSecret($clientId);
        }

        return null;
    }

    /**
     * @param string $authHeader
     * @return string|null
     */

    public function parseAuthHeader($authHeader)
    {
        if (!empty($authHeader)) {
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * @param string $authHeader
     * @return boolean
     */

    public function logout($authHeader)
    {
        $success = false;

        $accessToken = $this->accessTokenManager->findTokenByToken(
            $this->parseAuthHeader(
                $authHeader
            )
        );

        if ($accessToken) {
            $this->accessTokenManager->deleteToken($accessToken);
            $success = true;
        }

        return $success;
    }

    /**
     * @param string $authHeader
     * @return boolean
     */

    public function refreshToken($authHeader)
    {
        $success = false;

        $accessToken = $this->accessTokenManager->findTokenByToken(
            $this->parseAuthHeader(
                $authHeader
            )
        );

        if ($accessToken) {
            $expiresAt = 86400 + time(); # 1 Day in seconds
            $accessToken->setExpiresAt($expiresAt);
            $this->accessTokenManager->updateToken($accessToken);
            $success = true;
        }

        return $success;
    }

    /**
     * @param string $clientId
     * @return string|null
     */

    public function getClientSecret($clientId)
    {
        /** @var $client Client */
        $client = $this->clientManager->findClientByPublicId($clientId);

        if ($client) {
            return $client->getSecret();
        }

        return null;
    }

    /**
     * @return string
     */

    public function initAuthContext()
    {
        $client = $this->clientManager->findClientBy(['allowedGrantTypes' => 'password']);

        if (!$client) {
            $client = $this->clientManager->createClient();
            $client->setAllowedGrantTypes('password');
            $this->clientManager->updateClient($client);
        }

        return $client->getPublicId();
    }
}
