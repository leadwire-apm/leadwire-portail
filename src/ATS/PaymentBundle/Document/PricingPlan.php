<?php
namespace ATS\PaymentBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\EmbeddedDocument
 */
class PricingPlan
{
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="name")
     * @JMS\Type("string")
     * @JMS\Expose
     */
    private $name;

    /**
     * @var integer
     *
     * @ODM\Field(type="string", name="token")
     * @JMS\Type("string")
     * @JMS\Expose
     */
    private $token;

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     * @param string
     *
     * @return PricingPlan
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get token
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set token
     * @param $token
     * @return PricingPlan
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }
}
