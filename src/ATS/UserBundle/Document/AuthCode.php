<?php

namespace ATS\UserBundle\Document;

use ATS\UserBundle\Document\User;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use FOS\OAuthServerBundle\Document\AuthCode as BaseAuthCode;
use FOS\OAuthServerBundle\Model\ClientInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ODM\Document
 * @author Hounaida ZANNOUN <hzannoun@ats-digital.com>
 */
class AuthCode extends BaseAuthCode
{
    /**
     * @ODM\Id
     */
    protected $id;

    /**
     * @ODM\ReferenceOne(targetDocument="Client")
     */
    protected $client;

    /**
     * @ODM\ReferenceOne(targetDocument="ATS\UserBundle\Document\User")
     */
    protected $user;

    /**
     * Get Id
     * @return $this
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get User
     * @return User $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set User
     * @return $this $user
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get Client
     * @return Client $client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set Client
     * @return $this $client
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;

        return $this;
    }
}
