<?php declare (strict_types = 1);

namespace AppBundle\Document;

use AppBundle\Document\Application;
use AppBundle\Document\User;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\Document(repositoryClass="AppBundle\Repository\ApplicationPermissionRepository")
 * @ODM\HasLifecycleCallbacks
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 *
 */
class ApplicationPermission
{
    public const ACCESS_OWNER = "OWNER";
    public const ACCESS_GUEST = "GUEST";
    public const ACCESS_DEMO = "DEMO";
    public const ACCESS_DENIED = "DENIED";

    /**
     * @ODM\Id(strategy="auto")
     *
     * @var \MongoId
     */
    private $id;

    /**
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\User", storeAs="dbRef")
     *
     * @var User
     */
    private $user;

    /**
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\Application", storeAs="dbRef")
     *
     * @var Application
     */
    private $application;

    /**
     * @ODM\Field(type="string")
     *
     * @var string
     */
    private $access;

    /**
     * @ODM\Field(type="date")
     *
     * @var \DateTime
     */
    private $modifiedAt;

    /**
     * Get the value of id
     *
     * @return  \MongoId
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of user
     *
     * @return  User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @param  User  $user
     *
     * @return  self
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of application
     *
     * @return  Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set the value of application
     *
     * @param  Application  $application
     *
     * @return  self
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;

        return $this;
    }

    /**
     * Get the value of access
     *
     * @return  string
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * Set the value of access
     *
     * @param  string  $access
     *
     * @return  self
     */
    public function setAccess(string $access)
    {
        $this->access = $access;

        return $this;
    }

    /**
     * Get the value of modifiedAt
     *
     * @return  \DateTime
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * Set the value of modifiedAt
     *
     * @param  \DateTime  $modifiedAt
     *
     * @return  self
     */
    public function setModifiedAt(\DateTime $modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }
}
