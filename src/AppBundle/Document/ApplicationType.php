<?php declare (strict_types = 1);

namespace AppBundle\Document;

use AppBundle\Document\App;
use JMS\Serializer\Annotation as JMS;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(repositoryClass="AppBundle\Repository\ApplicationTypeRepository")
 * @ODM\HasLifecycleCallbacks
 * @JMS\ExclusionPolicy("all")
 *
 */
class ApplicationType
{
    const DEFAULT_TYPE = 'DEFAULT';

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
     * @ODM\Field(type="string", name="name")
     * @JMS\Type("string")
     * @JMS\Expose
     */
    private $name;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="installation")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"Default"})
     */
    private $installation;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"Default"})
     */
    private $description;

    /**
     * @var string
     * @deprecated 1.3
     * @ODM\Field(type="string", name="agent")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"Default", "full"})
     */
    private $agent;

    /**
     * @var Collection
     * @ODM\ReferenceMany(targetDocument="AppBundle\Document\Application", inversedBy="type", storeAs="dbRef")
     * @JMS\Groups({"full"})
     */
    public $apps;

    /**
     * @var Collection
     *
     * @ODM\ReferenceMany(targetDocument="AppBundle\Document\MonitoringSet", storeAs="dbRef")
     * @JMS\Expose
     * @JMS\Type("ArrayCollection   <AppBundle\Document\MonitoringSet>")
     */
    private $monitoringSets;

    /**
     * @var int
     * @ODM\Field(type="integer")
     * @JMS\Type("integer")
     * @JMS\Expose
     */
    private $version;

    /**
     * @var bool
     * @JMS\Type("boolean")
     * @JMS\Expose
     */
    private $canApplyChanges;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->monitoringSets = new ArrayCollection();
        $this->canApplyChanges = false;
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
     * @return ApplicationType
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get installation
     *
     * @return string
     */
    public function getInstallation()
    {
        return $this->installation;
    }

    /**
     * Set installation
     * @param string $installation
     *
     * @return ApplicationType
     */
    public function setInstallation($installation)
    {
        $this->installation = $installation;
        return $this;
    }

    /**
     * Get agent
     * @deprecated 1.3
     * @return string
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * Set agent
     * @deprecated 1.3
     * @param string $agent
     *
     * @return ApplicationType
     */
    public function setAgent($agent)
    {
        $this->agent = $agent;
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

    /**
     * @JMS\VirtualProperty()
     *
     * @return boolean
     */
    public function isWellDefined(): bool
    {
        // TODO impement this
        return false;
    }

    /**
     * @JMS\VirtualProperty()
     *
     * @return boolean
     */
    public function canInit(): bool
    {
        // TODO implement this
        return true;
    }

    /**
     * Get the value of monitoringSets
     */
    public function getMonitoringSets()
    {
        return $this->monitoringSets->toArray();
    }

    /**
     * Undocumented function
     *
     * @param MonitoringSet $ms
     *
     * @return self
     */
    public function addMonitoringSet(MonitoringSet $ms)
    {
        if ($this->monitoringSets->contains($ms) === false) {
            $this->monitoringSets->add($ms);
        }

        return $this;
    }

    /**
     * Get the value of description
     *
     * @return  string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @param  string  $description
     *
     * @return  self
     */
    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of version
     *
     * @return  int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set the value of version
     *
     * @param  int  $version
     *
     * @return  self
     */
    public function setVersion(int $version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return self
     */
    public function incrementVersion()
    {
        $this->version += 1;
        $this->canApplyChanges = true;

        return $this;
    }

    /**
     * Get the value of canApplyChanges
     *
     * @return  bool
     */
    public function getCanApplyChanges()
    {
        return $this->canApplyChanges;
    }

    /**
     * Set the value of canApplyChanges
     *
     * @param  bool  $canApplyChanges
     *
     * @return  self
     */
    public function setCanApplyChanges(bool $canApplyChanges)
    {
        $this->canApplyChanges = $canApplyChanges;

        return $this;
    }

    public function resetMonitoringSets()
    {
        $this->monitoringSets->clear();
    }
}
