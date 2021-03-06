<?php declare (strict_types = 1);

namespace AppBundle\Service;

use Firebase\JWT\JWT;
use AppBundle\Document\User;

class JWTHelper
{

    private $domain;
    private $secret;

    public function __construct(string $domain = '', string $secret = '')
    {
        $this->domain = $domain;
        $this->secret = $secret;
    }
    /**
     *
     * @param string $jwt
     * @param string $secret
     *
     * @return object
     */
    public function decode(string $jwt, string $secret)
    {
        $token = JWT::decode($jwt, $secret, ['HS256']);

        return $token;
    }

    /**
     * @param string $username
     * @param string $userIndex
     *
     * @return string
     */
    public function encode(string $username = "adm-portail", string $userIndex = "adm-portail"): string
    {
        $token = [
            'host' => $this->domain,
            'user' => $userIndex,
            'name' => $username,
            'iat' => time(),
            'exp' => time() +36000,
            'nbf' => time(),
        ];

        return JWT::encode($token, $this->secret);
    }

    /**
     * @deprecated 1.1
     * @param User $user
     *
     * @return string
     */
    public function getAuthorizationHeader(?User $user = null)
    {
        if ($user instanceof User) {
            return $this->encode(
                $user->getUsername(),
                $user->getIndex()
            );
        }

        return $this->encode();
    }
}
