<?php declare(strict_types=1);

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use ATS\CoreBundle\Annotation as ATS;
use AppBundle\Document\User;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique;

/**
 * @ODM\Document(repositoryClass="AppBundle\Repository\AppRepository")
 * @ODM\HasLifecycleCallbacks
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 * @ATS\ApplicationView
 * @Unique(fields={"name"})

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
     * @JMS\Groups({"Default"})
     */
    private $uuid;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="name")
     * @Assert\Length(min=5, max=32)
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"Default"})
     */
    private $name;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="description")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"Default"})
     */
    private $description;


    /**
     * @var string
     *
     * @ODM\Field(type="string", name="email")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"Default"})
     */
    private $email;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="paymentData")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"Default"})
     */
    private $paymentData;

    /**
     * @var boolean
     * @JMS\Expose
     * @JMS\Type("boolean")
     * @ODM\Field(type="boolean", name="isEnabled")
     */
    private $isEnabled;


    /**
     * @var boolean
     * @JMS\Groups({"never"})
     * @JMS\Expose
     * @JMS\Type("boolean")
     * @ODM\Field(type="boolean", name="isRemoved")
     */
    private $isRemoved;

    /**
     * @var User
     *
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\User", name="owner", cascade={"persist"}, inversedBy="myApps")
     * @JMS\Type("AppBundle\Document\User")
     * @JMS\Expose
     * @JMS\Groups({"full", "Default"})
     */
    private $owner;


    /**
     * @var ApplicationType
     *
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\ApplicationType", name="type", cascade={"persist"})
     * @JMS\Type("AppBundle\Document\ApplicationType")
     * @JMS\Expose
     * @JMS\Groups({"full", "Default"})
     */
    private $type;


    /** @ODM\ReferenceMany(targetDocument="Invitation", mappedBy="app")
     * @JMS\Type("array<AppBundle\Document\Invitation>")
     * @JMS\Expose
     * @JMS\Groups({"full", "Default"})
     */
    public $invitations;


    public $dashboards;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->invitations = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Get type
     * @return ApplicationType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     * @param ApplicationType $type
     * @return $this
     */
    public function setType(ApplicationType $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get isEnabled
     * @return boolean
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Set isEnabled
     * @param boolean $isEnabled
     * @return $this
     */
    public function setIsEnabled(bool $isEnabled)
    {
        $this->isEnabled = $isEnabled;
        return $this;
    }

    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Get isRemoved
     * @return boolean
     */
    public function getIsRemoved()
    {
        return $this->isRemoved;
    }

    /**
     * Set type
     * @param boolean $isRemoved
     * @return $this
     */
    public function setIsRemoved(bool $isRemoved)
    {
        $this->isRemoved = $isRemoved;
        return $this;
    }

    /**
     * Get elastic index
     * @return string
     */
    public function getIndex()
    {
        return 'app_' . $this->getUuid();
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
