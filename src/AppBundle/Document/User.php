<?php declare (strict_types = 1);
namespace AppBundle\Document;

use AppBundle\Document\Application;
use ATS\PaymentBundle\Document\Plan;
use JMS\Serializer\Annotation as JMS;
use ATS\PaymentBundle\Document\Customer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Ramsey\Uuid\Uuid;

/**
 * @ODM\Document(repositoryClass="AppBundle\Repository\UserRepository")
 * @ODM\HasLifecycleCallbacks
 * @JMS\ExclusionPolicy("all")
 * @Unique(fields={"username"})
 * @Unique(fields={"email"})
 */
class User implements AdvancedUserInterface
{
    const DEFAULT_ROLE = "ROLE_USER";
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * @var \MongoId
     *
     * @ODM\Id("strategy=auto")
     * @JMS\Expose
     * @JMS\Type("string")
     */
    protected $id;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     */
    protected $username;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $password;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $salt;

    /**
     * @var bool
     *
     * @ODM\Field(type="boolean")
     * @JMS\Expose
     */
    private $active;

    /**
     * @var \DateTime
     *
     * @ODM\Field(type="date")
     */
    private $expireAt;

    /**
     * @var array
     *
     * @ODM\Field(type="collection")
     * @JMS\Type("array")
     * @JMS\Expose
     */
    private $roles;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="uuid")
     * @ODM\Index(unique=true)
     * @JMS\Type("string")
     * @JMS\Expose
     */
    private $uuid;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="avatar")

     * @JMS\Type("string")
     * @JMS\Expose
     */
    private $avatar;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $name;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="company")
     * @JMS\Type("string")
     * @JMS\Expose
     */
    private $company;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="contact")
     * @JMS\Type("string")
     * @JMS\Expose
     */
    private $contact;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="contactPreference")
     * @JMS\Type("string")
     * @JMS\Expose
     */
    private $contactPreference;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="subscriptionId")
     * @JMS\Type("string")
     * @JMS\Expose
     */
    private $subscriptionId;

    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="isEmailValid")
     * @JMS\Type("boolean")
     * @JMS\Expose
     */
    private $emailValid;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="email")

     * @JMS\Type("string")
     * @JMS\Expose
     */
    private $email;

    /**
     * @var boolean
     * @ODM\Field(type="boolean", name="acceptNewsLetter")
     * @JMS\Type("boolean")
     * @JMS\Expose
     */
    private $acceptNewsLetter;

    /**
     * @ODM\ReferenceMany(targetDocument="AppBundle\Document\Invitation", mappedBy="user", storeAs="dbRef")
     * @JMS\Type("array<AppBundle\Document\Invitation>")
     * @JMS\Expose
     */
    public $invitations;

    /**
     * @ODM\ReferenceMany(targetDocument="AppBundle\Document\Application", mappedBy="owner", storeAs="dbRef")
     * @JMS\Type("array<AppBundle\Document\Application>")
     * @JMS\Expose
     */
    private $applications;

    /**
     * @var ?Application
     *
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\Application", name="defaultApp", cascade={"persist"}, nullable=true, storeAs="dbRef")
     * @JMS\Type("AppBundle\Document\Application")
     * @JMS\Expose
     */
    private $defaultApplication = null;

    /**
     * @var Plan|null
     *
     * @ODM\ReferenceOne(targetDocument="ATS\PaymentBundle\Document\Plan", name="plan", cascade={"persist"}, storeAs="dbRef")
     * @JMS\Type("ATS\PaymentBundle\Document\Plan")
     * @JMS\Expose
     */
    private $plan = null;

    /**
     * @var Customer|null
     *
     * @ODM\ReferenceOne(targetDocument="ATS\PaymentBundle\Document\Customer", name="customer", cascade={"persist"}, storeAs="dbRef")
     * @JMS\Type("ATS\PaymentBundle\Document\Customer")
     * @JMS\Expose
     */
    private $customer = null;

    /**
     * @var bool
     *
     * @ODM\Field(type="bool")
     * @JMS\Type("boolean")
     * @JMS\Expose
     */
    private $deleted;

    /**
     * @var bool
     *
     * @ODM\Field(type="bool")
     * @JMS\Type("boolean")
     * @JMS\Expose
     */
    private $locked;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     * @JMS\Type("string")
     * @JMS\Expose
     */
    private $lockMessage;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->roles = [];
        $this->locked = false;
        $this->deleted = false;

        $this->applications = new ArrayCollection();
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
     * @return User
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

    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set salt
     *
     * @param string $salt
     *
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set active
     *
     * @param bool $active
     *
     * @return User
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Activates a user
     *
     * @return User
     */
    public function activate()
    {
        return $this->setActive(true);
    }

    /**
     * Deactivates a user
     *
     * @return User
     */
    public function deactivate()
    {
        return $this->setActive(false);
    }

    /**
     * Set roles
     *
     * @param array $roles
     *
     * @return User
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {

        if (in_array(self::DEFAULT_ROLE, $this->roles) === false) {
            array_push($this->roles, self::DEFAULT_ROLE);
        }

        return $this->roles;
    }

    /**
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {
        return in_array($role, $this->roles);
    }

    /**
     * Promote user roles
     *
     * @param string $role
     *
     * @return User
     */
    public function promote($role)
    {
        if (in_array($role, $this->roles) === false) {
            array_push($this->roles, $role);
        }

        return $this;
    }

    /**
     * Revoke user roles
     *
     * @param string $role
     *
     * @return User
     */
    public function revoke($role)
    {
        if (null === $this->roles) {
            $this->roles = [];
        }

        $this->roles = array_diff($this->roles, [$role]);

        return $this;
    }

    /**
     * Set expireAt
     *
     * @param \DateTime $expireAt
     *
     * @return User
     */
    public function setExpireAt(\DateTime $expireAt)
    {
        $this->expireAt = $expireAt;

        return $this;
    }

    /**
     * Get expireAt
     *
     * @return \DateTime|null
     */
    public function getExpireAt()
    {
        return $this->expireAt;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * Set avatar
     *
     * @param string $avatar
     *
     * @return User
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Get login
     * @return string
     */
    public function getLogin()
    {
        return $this->getUsername();
    }

    /**
     * Set company
     *
     * @param string $company
     *
     * @return User
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

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
     * Set acceptNewsLetter
     *
     * @param bool $acceptNewsLetter
     *
     * @return User
     */
    public function setAcceptNewsLetter($acceptNewsLetter)
    {
        $this->acceptNewsLetter = $acceptNewsLetter;

        return $this;
    }

    /**
     * Get acceptNewsLetter
     *
     * @return bool
     */
    public function getAcceptNewsLetter()
    {
        return $this->acceptNewsLetter;
    }

    /**
     * Set emailValid
     *
     * @param bool $emailValid
     *
     * @return self
     */
    public function setEmailValid($emailValid): self
    {
        $this->emailValid = $emailValid;

        return $this;
    }

    /**
     * Get emailValid
     *
     * @return bool
     */
    public function isEmailValid()
    {
        return $this->emailValid;
    }

    /**
     * Set contact
     *
     * @param string $contact
     *
     * @return User
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return string
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set contactPreference
     *
     * @param string $contactPreference
     *
     * @return User
     */
    public function setContactPreference($contactPreference)
    {
        $this->contactPreference = $contactPreference;

        return $this;
    }

    /**
     * Get contactPreference
     *
     * @return string
     */
    public function getContactPreference()
    {
        return $this->contactPreference;
    }

    /**
     * Get subscriptionId
     * @return string
     */
    public function getSubscriptionId()
    {
        return $this->subscriptionId;
    }

    /**
     * Set subscriptionId
     * @param string $subscriptionId
     * @return $this
     */
    public function setSubscriptionId(string $subscriptionId)
    {
        $this->subscriptionId = $subscriptionId;
        return $this;
    }

    /**
     * @return ?Application
     */
    public function getDefaultApplication(): ?Application
    {
        return $this->defaultApplication;
    }

    /**
     * @param ?Application $defaultApplication
     *
     * @return User
     */
    public function setDefaultApplication($defaultApplication): self
    {
        $this->defaultApplication = $defaultApplication;

        return $this;
    }

    public function getPlan(): ?Plan
    {
        return $this->plan;
    }

    public function setPlan(?Plan $plan): self
    {
        $this->plan = $plan;

        return $this;
    }

    /**
     * Get Customer
     *
     * @return ?Customer
     */
    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @deprecated 1.1
     *
     * @return string
     */
    public function getIndex()
    {
        return "user_" . $this->uuid;
    }

    /**
     * @JMS\VirtualProperty()
     *
     * @return string
     */
    public function getUserIndex(): string
    {
        if (Uuid::isValid($this->uuid) === true) {
            return "user_" . $this->uuid;
        } else {
            return $this->username;
        }
    }

    /**
     * @JMS\VirtualProperty()
     *
     * @return string
     */
    public function getAllUserIndex(): string
    {
        if (Uuid::isValid($this->uuid) === true) {
            return "all_user_" . $this->uuid;
        } else {
            return $this->username;
        }
    }

    /**
     * Get the value of deleted
     *
     * @return  bool|null
     */
    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    /**
     * Set the value of deleted
     *
     * @param  bool  $deleted
     *
     * @return  self
     */
    public function setDeleted(bool $deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get the value of locked
     *
     * @return  bool|null
     */
    public function isLocked(): ?bool
    {
        return $this->locked;
    }

    /**
     * Set the value of locked
     *
     * @param  bool  $locked
     *
     * @return  self
     */
    public function setLocked(bool $locked): self
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Toggles value of locked
     *
     * @return  self
     */
    public function toggleLock(): self
    {
        $this->locked = !$this->locked;

        return $this;
    }
    /**
     * Get the value of lockMessage
     *
     * @return  string
     */
    public function getLockMessage(): ?string
    {
        return $this->lockMessage;
    }

    /**
     * Set the value of lockMessage
     *
     * @param  string  $lockMessage
     *
     * @return  self
     */
    public function setLockMessage(string $lockMessage): self
    {
        $this->lockMessage = $lockMessage;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function isAccountNonExpired()
    {
        if ($this->getExpireAt() === null) {
            return true;
        }

        return $this->getExpireAt() > (new \DateTime());
    }

    /**
     * {@inheritDoc}
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @JMS\VirtualProperty()
     */
    public function isEnabled()
    {
        return $this->active;
    }

    /**
     * Get the value of applications
     */
    public function getApplications()
    {
        return $this->applications;
    }

    /**
     * Set the value of applications
     *
     * @return  self
     */
    public function setApplications($applications)
    {
        $this->applications = $applications;

        return $this;
    }

    public function addApplication(Application $application): self
    {
        if ($this->applications !== null && $this->applications->contains($application) === false) {
            $this->applications->addElement($application);
        }

        return $this;
    }
}
