<?php

namespace ATS\UserBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use FOS\OAuthServerBundle\Document\Client as BaseClient;

/**
 * @ODM\Document
 * @author Hounaida ZANNOUN <hzannoun@ats-digital.com>
 */
class Client extends BaseClient
{
    /**
     * @ODM\Id
     */
    protected $id;

    /**
     * Get Id
     * @return $this
     */
    public function getId()
    {
        return $this->id;
    }
}
