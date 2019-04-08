<?php declare (strict_types = 1);

namespace AppBundle\Document;

use AppBundle\Document\App;
use JMS\Serializer\Annotation as JMS;
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
    const DEFAULT_TYPE = 'Java';

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
     * @ODM\ReferenceMany(targetDocument="AppBundle\Document\Template", mappedBy="applicationType", storeAs="dbRef", cascade={"remove"})
     *
     * @var ArrayCollection
     */
    private $templates;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="agent")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"Default", "full"})
     */
    private $agent;

    /**
     * @ODM\ReferenceMany(targetDocument="AppBundle\Document\Application", inversedBy="type", storeAs="dbRef")
     * @JMS\Groups({"full"})
     */
    public $apps;

    /**
     * Constructor
     */
    public function __construct()
    {
        // auto-generated stub
        $this->templates = new ArrayCollection();
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
     * Get template
     *
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * Get agent
     *
     * @return string
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * Set agent
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

    public function getMonitoringSets()
    {
        return array_unique(
            array_map(
                function (Template $template) {
                    return $template->getMonitoringSet();
                },
                $this->templates->toArray()
            )
        );
    }
}
