<?php declare (strict_types = 1);

namespace ATS\PaymentBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\Document(repositoryClass="ATS\PaymentBundle\Repository\CustomerRepository")
 * @ODM\HasLifecycleCallbacks
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 */
class Customer
{
    /**
     * @var \MongoId
     *
     * @ODM\Id("strategy=auto")
     * @JMS\Type("string")
     * @JMS\Expose
     */
    private $id;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="name")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $name;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="email")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $email;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="gatewayToken")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $gatewayToken;

    /**
     * Constructor
     */
    public function __construct()
    {
        // auto-generated stub
    }

    /**
     * Get id
     *
     * @return \MongoId
     */
    public function getId()
    {
        return $this->id;
    }

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
     * @return Customer
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email
     * @param string
     *
     * @return Customer
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get gatewayToken
     *
     * @return string
     */
    public function getGatewayToken()
    {
        return $this->gatewayToken;
    }

    /**
     * Set gatewayToken
     * @param string
     *
     * @return Customer
     */
    public function setGatewayToken($gatewayToken)
    {
        $this->gatewayToken = $gatewayToken;
        return $this;
    }

    /**
     * Returns string representation of the object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->id;
    }
}
