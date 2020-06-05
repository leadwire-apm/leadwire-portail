<?php declare (strict_types=1);

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use AppBundle\Document\Environment;
use AppBundle\Document\Application;

/**
 * Class Access Level
 *
 * @ODM\Document(repositoryClass="AppBundle\Repository\AccessLevelRepository")
 * @ODM\HasLifecycleCallbacks
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 *
 * @author Wajih WERIEMI <wajih@ats-digital.com>
 */
class AccessLevel
{
    const ACCESS = "ACCESS";
    const ALERT  = "ALERT";

    const VIEWER  = "VIEWER ";
    const EDITOR = "EDITOR";
    const ADMIN = "ADMIN";

    /**
     * @var \MongoId
     *
     * @ODM\Id("strategy=auto")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"acl"})
     */
    private $id;

    /**
     * @var User
     *
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\User", mappedBy="accessLevels", storeAs="dbRef")
     * @JMS\Type("AppBundle\Document\User")
     * @JMS\Expose
     * @JMS\Groups({"Default", "acl"})
     */
    private $user;

    /**
     * @var Environment
     *
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\Environment", storeAs="dbRef")
     * @JMS\Type("AppBundle\Document\Environment")
     * @JMS\Expose
     * @JMS\Groups({"Default", "acl"})
     */
    private $environment;

    /**
     * @var Application
     *
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\Application", storeAs="dbRef")
     * @JMS\Type("AppBundle\Document\Application")
     * @JMS\Expose
     * @JMS\Groups({"Default", "acl"})
     */
    private $application;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"Default", "acl"})
     */
    private $access;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"Default", "acl"})
     */
    private $level;

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
     * Set user
     *
     * @param User $user
     *
     * @return AccessLevel
     */
    public function setUser(User $user)
    {
        $this->user = $user;

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
     * Set environment
     *
     * @param Environment $environment
     *
     * @return AccessLevel
     */
    public function setEnvironment(Environment $environment)
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * Get environment
     *
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Set application
     *
     * @param Application $application
     *
     * @return AccessLevel
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;

        return $this;
    }

    /**
     * Get application
     *
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set access
     *
     * @param string $access
     *
     * @return AccessLevel
     */
    public function setAccess($access)
    {
        $this->access = $access;

        return $this;
    }

    /**
     * Get access
     *
     * @return boolean
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * Get level
     *
     * @return boolean
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set level
     *
     * @param string $level
     *
     * @return AccessLevel
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }
}
