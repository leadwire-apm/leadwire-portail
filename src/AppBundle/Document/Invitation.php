<?php declare (strict_types = 1);

namespace AppBundle\Document;

use AppBundle\Document\Application;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\Document(repositoryClass="AppBundle\Repository\InvitationRepository")
 * @ODM\HasLifecycleCallbacks
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 */
class Invitation
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
     * @var Application
     *
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\Application", name="app", cascade={"persist"}, inversedBy="invitations", storeAs="dbRef")
     * @JMS\Type("AppBundle\Document\Application")
     * @JMS\Expose
     */
    private $application;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="email")
     * @JMS\Type("string")
     * @JMS\Expose
     */
    private $email;

    /**
     * @var User
     *
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\User", name="user", cascade={"persist"}, inversedBy="otherApps", storeAs="dbRef")
     * @JMS\Type("AppBundle\Document\User")
     * @JMS\Expose
     */
    private $user = null;

    /**
     * @var bool
     *
     * @ODM\Field(type="boolean", name="isPending")
     * @JMS\Type("boolean")
     * @JMS\Expose
     */
    private $pending = true;

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
     * Get app
     *
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set app
     * @param Application $application
     *
     * @return Invitation
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;

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
     * @return Invitation
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get pending
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->pending;
    }

    /**
     * Set pending
     * @param bool $pending
     *
     * @return Invitation
     */
    public function setPending($pending)
    {
        $this->pending = $pending;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user
     * @param User $user
     *
     * @return Invitation
     */
    public function setUser(User $user)
    {
        $this->user = $user;
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
