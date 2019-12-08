<?php declare (strict_types=1);

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use AppBundle\Document\Environment;
use AppBundle\Document\Application;

/**
 * Class Access Level
 *
 * @ODM\EmbeddedDocument
 * @ODM\HasLifecycleCallbacks
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 *
 * @author Wajih WERIEMI <wajih@ats-digital.com>
 */
class AccessLevel
{
    /**
     * @var Environment
     *
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\Environment", storeAs="dbRef")
     * @JMS\Type("AppBundle\Document\Environment")
     * @JMS\Expose
     * @JMS\Groups({"Default"})
     */
    private $environment;

    /**
     * @var Application
     *
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\Application", storeAs="dbRef")
     * @JMS\Type("AppBundle\Document\Application")
     * @JMS\Expose
     * @JMS\Groups({"Default"})
     */
    private $application;

    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean")
     * @JMS\Type("boolean")
     * @JMS\Expose
     * @JMS\Groups({"Default"})
     */
    private $read;

    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean")
     * @JMS\Type("boolean")
     * @JMS\Expose
     * @JMS\Groups({"Default"})
     */
    private $write;

    /**
     * Constructor
     *
     * @param Environment $environment
     * @param Application $application
     * @param boolean     $read
     * @param boolean     $write
     */
    public function __construct(Environment $environment, Application $application, $read = true, $write = false)
    {
        $this->environment = $environment;
        $this->application = $application;
        $this->read = $read;
        $this->write = $write;
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
     * Get read access
     *
     * @return boolean
     */
    public function getRead()
    {
        return $this->read;
    }

    /**
     * Grand read access
     *
     * @return AccessLevel
     */
    public function grantReadAccess()
    {
        $this->read = true;
    }

    /**
     * Revoke read access
     *
     * @return AccessLevel
     */
    public function revokeReadAccess()
    {
        $this->read = false;
    }

    /**
     * Get read access
     *
     * @return boolean
     */
    public function getWrite()
    {
        return $this->write;
    }

    /**
     * Grand write access
     *
     * @return AccessLevel
     */
    public function grantWriteAccess()
    {
        $this->write = true;
    }

    /**
     * Revoke write access
     *
     * @return AccessLevel
     */
    public function revokeWriteAccess()
    {
        $this->write = false;
    }
}
