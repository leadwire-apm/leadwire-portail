<?php declare (strict_types = 1);

namespace AppBundle\Document;

use AppBundle\Document\User;
use AppBundle\Document\Environment;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\ApplicationType", name="type", storeAs="dbRef")
     * @JMS\Type("AppBundle\Document\ApplicationType")
     * @JMS\Expose
     * @JMS\Groups({"full", "Default"})
     */
    private $type;

    /**
     * @var int
     *
     * @ODM\Field(type="int")
     * @JMS\Type("integer")
     * @JMS\Expose
     */
    private $deployedTypeVersion;

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
     * @var Collection
     *
     * @ODM\ReferenceMany(targetDocument="AppBundle\Document\Environment", cascade={"persist"}, inversedBy="applications", storeAs="dbRef")
     * @JMS\Expose
     * @JMS\Groups({"full", "Default"})
     * @JMS\Type("array<AppBundle\Document\Environment>")
     */
    private $environments;

    /**
     * @ODM\Field(type="date")
     * @JMS\Type("DateTime")
     * @JMS\Expose
     * @JMS\Groups({"full"})
     *
     * @var \DateTime
     */
    private $createdAt;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->invitations = new ArrayCollection();
        $this->environments = new ArrayCollection();
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

    /**
     * @JMS\VirtualProperty
     *
     * @return string
     */
    public function getApplicationIndex(): string
    {
        return "app_" . $this->uuid;
    }

    /**
     * @JMS\VirtualProperty
     *
     * @return string
     */
    public function getSharedIndex(): string
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

    /**
     * Get the value of environments
     */
    public function getEnvironments($toArray = true)
    {
        if ($this->environments === null) {
            $this->environments = new ArrayCollection();
        }

        return $toArray ? $this->environments->toArray() : $this->environments;
    }

    /**
     * Add  environment
     *
     * @param  Environment  $environment
     *
     * @return  self
     */
    public function addEnvironment(Environment $environment)
    {
        if ($this->environments == null) {
            $this->environments = new ArrayCollection();
        }
        if (!$this->environments->contains($environment)) {
            $this->environments->add($environment);
        }

        return $this;
    }

    /**
     * Get the value of createdAt
     *
     * @return  \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set the value of createdAt
     *
     * @param \DateTime  $createdAt
     *
     * @return  self
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get the value of deployedTypeVersion
     *
     * @return  int
     */
    public function getDeployedTypeVersion(): int
    {
        return $this->deployedTypeVersion;
    }

    /**
     * Set the value of deployedTypeVersion
     *
     * @param  int  $deployedTypeVersion
     *
     * @return  self
     */
    public function setDeployedTypeVersion(int $deployedTypeVersion)
    {
        $this->deployedTypeVersion = $deployedTypeVersion;

        return $this;
    }

    /**
     * @JMS\VirtualProperty
     *
     * @return boolean
     */
    public function canApplyChanges(): bool
    {
        return $this->deployedTypeVersion !== $this->getType()->getVersion();
    }
}
