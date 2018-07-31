<?php declare(strict_types=1);

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use ATS\CoreBundle\Annotation as ATS;
use AppBundle\Document\User;

/**
 * @ODM\Document(repositoryClass="AppBundle\Repository\AppRepository")
 * @ODM\HasLifecycleCallbacks
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 * @ATS\ApplicationView
 */
class App
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
     * @ODM\Field(type="string", name="uuid")
     * @ODM\Index(unique=true)
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $uuid;

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
     * @ODM\Field(type="string", name="type")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $type;
    /**
     * @var string
     *
     * @ODM\Field(type="string", name="description")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $description;

    /**
     * @var integer
     *
     * @ODM\Field(type="integer", name="activationCode")
     * @JMS\Type("integer")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $activationCode;

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
     * @ODM\Field(type="string", name="paymentData")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $paymentData;

    /**
     * @var User
     *
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\User", name="owner", cascade={"persist"})
     * @JMS\Type("AppBundle\Document\User")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $owner;

    /**
     * @var array
     *
     * @ODM\Field(type="hash", name="dashboards")
     * @JMS\Type("array")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $dashboards;

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
     * Set uuid
     *
     * @param string $uuid
     *
     * @return App
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }


    /**
     * Get uuid
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
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
     * @return App
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     * @param string
     *
     * @return App
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get activationCode
     *
     * @return integer
     */
    public function getActivationCode()
    {
        return $this->activationCode;
    }

    /**
     * Set activationCode
     * @param integer
     *
     * @return App
     */
    public function setActivationCode($activationCode)
    {
        $this->activationCode = $activationCode;
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
     * @return App
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get paymentData
     *
     * @return string
     */
    public function getPaymentData()
    {
        return $this->paymentData;
    }

    /**
     * Set paymentData
     * @param string
     *
     * @return App
     */
    public function setPaymentData($paymentData)
    {
        $this->paymentData = $paymentData;
        return $this;
    }

    /**
     * Get owner
     *
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set owner
     * @param User
     *
     * @return App
     */
    public function setOwner(User $owner)
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * Get dashboards
     *
     * @return array
     */
    public function getDashboards()
    {
        return $this->dashboards;
    }

    /**
     * Set dashboards
     * @param array
     *
     * @return App
     */
    public function setDashboards($dashboards)
    {
        $this->dashboards = $dashboards;
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
