<?php declare (strict_types = 1);

namespace AppBundle\Document;

use AppBundle\Document\User;
use JMS\Serializer\Annotation as JMS;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique;

/**
 * @ODM\Document(repositoryClass="AppBundle\Repository\ApplicationRepository")
 * @ODM\HasLifecycleCallbacks
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 *
 */
class Application
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
     * @ODM\UniqueIndex()
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"Default"})
     */
    private $uuid;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="name")
     * @ODM\UniqueIndex()
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
     * @ODM\Field(type="boolean", name="enabled")
     */
    private $enabled;

    /**
     * @var boolean
     * @JMS\Groups({})
     * @JMS\Expose
     * @JMS\Type("boolean")
     * @ODM\Field(type="boolean", name="removed")
     */
    private $removed;

    /**
     * @var User
     *
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\User", name="owner", cascade={"persist"}, inversedBy="applications", storeAs="dbRef")
     * @JMS\Type("AppBundle\Document\User")
     * @JMS\Expose
     * @JMS\Groups({"full", "Default"})
     */
    private $owner;

    /**
     * @var ApplicationType
     *
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\ApplicationType", name="type", cascade={"persist"}, storeAs="dbRef")
     * @JMS\Type("AppBundle\Document\ApplicationType")
     * @JMS\Expose
     * @JMS\Groups({"full", "Default"})
     */
    private $type;

    /**
     * @ODM\ReferenceMany(targetDocument="AppBundle\Document\Invitation", mappedBy="app", storeAs="dbRef")
     * @JMS\Type("array<AppBundle\Document\Invitation>")
     * @JMS\Expose
     */
    private $invitations;

    /**
     * @ODM\Field(type="bool")
     * @JMS\Type("boolean")
     * @JMS\Expose
     * @JMS\Groups({"full"})
     *
     * @var bool
     */
    private $demo;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->invitations = new ArrayCollection();
        $this->enabled = false;
        $this->removed = false;
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
     * @return Application
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
     * @param string $name
     *
     * @return Application
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
     * @param string $description
     *
     * @return Application
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
     * @param string $email
     *
     * @return Application
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
     * @param string $paymentData
     *
     * @return Application
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
     * @param User $owner
     *
     * @return Application
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
     * Get enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set enabled
     * @param boolean $enabled
     *
     * @return Application
     */
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     *
     * @return self
     */
    public function toggleEnabled(): self
    {
        $this->enabled = !$this->enabled;

        return $this;
    }

    /**
     * Get removed
     *
     * @return boolean
     */
    public function isRemoved()
    {
        return $this->removed;
    }

    /**
     * Set type
     * @param boolean $removed
     *
     * @return Application
     */
    public function setRemoved(bool $removed)
    {
        $this->removed = $removed;

        return $this;
    }

    /**
     * Get elastic index
     * @deprecated 1.1
     * @return string
     */
    public function getIndex()
    {
        return 'app_' . $this->getUuid();
    }

    public function getApplicationIndex()
    {
        return "app_". $this->uuid;
    }

    public function getSharedIndex()
    {
        return "shared_" . $this->uuid;
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

    /**
     * Get the value of demo
     *
     * @return  bool|null
     */
    public function isDemo(): ?bool
    {
        return $this->demo;
    }

    /**
     * Set the value of demo
     *
     * @param  bool  $demo
     *
     * @return  self
     */
    public function setDemo(bool $demo): self
    {
        $this->demo = $demo;

        return $this;
    }

    /**
     * Get the value of invitations
     */
    public function getInvitations()
    {
        return $this->invitations;
    }
}
