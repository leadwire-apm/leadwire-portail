<?php declare(strict_types=1);

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use ATS\CoreBundle\Annotation as ATS;
use AppBundle\Document\App;

/**
 * @ODM\Document(repositoryClass="AppBundle\Repository\InvitationRepository")
 * @ODM\HasLifecycleCallbacks
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 * @ATS\ApplicationView
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
     * @var App
     *
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\App", name="app", cascade={"persist"}, inversedBy="invitations")
     * @JMS\Type("AppBundle\Document\App")
     * @JMS\Expose
     * @JMS\Groups({"Default"})
     */
    private $app;

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
     * @var User
     *
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\User", name="user", cascade={"persist"}, inversedBy="otherApps")
     * @JMS\Type("AppBundle\Document\User")
     * @JMS\Expose
     * @JMS\Groups({"full"})
     */
    private $user = null;

    /**
     * @var bool
     *
     * @ODM\Field(type="boolean", name="isPending")
     * @JMS\Type("boolean")
     * @JMS\Expose
     * @JMS\Groups({"Default"})
     */
    private $isPending = true;


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
     * @return App
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * Set app
     * @param App
     *
     * @return Invitation
     */
    public function setApp(App $app)
    {
        $this->app = $app;
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
     * @return Invitation
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get isPending
     *
     * @return bool
     */
    public function getIsPending()
    {
        return $this->isPending;
    }

    /**
     * Set isPending
     * @param bool
     *
     * @return Invitation
     */
    public function setIsPending($isPending)
    {
        $this->isPending = $isPending;
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
     * @param User
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
