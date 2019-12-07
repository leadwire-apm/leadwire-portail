<?php declare (strict_types = 1);

namespace AppBundle\Document;

use AppBundle\Document\Application;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ODM\Document(repositoryClass="AppBundle\Repository\StatRepository")
 * @ODM\HasLifecycleCallbacks
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 */
class Environment
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
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     *
     * @var string
     */
    private $ip;

    /**
     * @ODM\ReferenceMany(targetDocument="AppBundle\Document\Application", mappedBy="environments", storeAs="dbRef")
     * @JMS\Type("array<AppBundle\Document\Application>")
     * @JMS\Expose
     *
     * @var ArrayCollection
     */
    private $applications;

    /**
     * @ODM\Field(type="boolean")
     * @JMS\Expose
     * @JMS\Type("boolean")
     *
     * @var boolean
     */
    private $default;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->default = false;
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
     * Set the value of name
     *
     * @param  string  $name
     *
     * @return  self
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of ip
     *
     * @param  string  $ip
     *
     * @return  self
     */
    public function setIp(string $ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get the value of ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }


    /**
     * Get the value of applications
     *
     * @return array
     */
    public function getApplications($toArray = true)
    {
        if ($this->applications == null) {
            $this->applications = new ArrayCollection();
        }

        return $toArray ? $this->applications->toArray() : $this->applications;
    }

    /**
     * Set the value of applications
     *
     * @param  array  $applications
     *
     * @return  self
     */
    public function setApplications(array $applications)
    {
        $this->applications = $applications;

        return $this;
    }

    /**
     * Set the value of application
     *
     * @param  Application  $application
     *
     * @return  self
     */
    public function addApplication(Application $application)
    {
        if ($this->applications == null) {
            $this->applications = new ArrayCollection();
        }
        if (!$this->getApplications()->contains($application)) {
            $this->applications->add($application);
        }

        return $this;
    }

    /**
     * Set default
     *
     * @param boolean $default
     *
     * @return  self
     */
    public function setDefault($default = true)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * Get default
     *
     * @return boolean
     */
    public function getDefault()
    {
        return $this->default;
    }
}
