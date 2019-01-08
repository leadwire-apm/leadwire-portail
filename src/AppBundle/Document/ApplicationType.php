<?php declare(strict_types=1);

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

use AppBundle\Document\App;

/**
 * @ODM\Document(repositoryClass="AppBundle\Repository\ApplicationTypeRepository")
 * @ODM\HasLifecycleCallbacks
 * @JMS\ExclusionPolicy("all")
 *
 */
class ApplicationType
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
     * @ODM\Field(type="string", name="name")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"Default"})
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
     * @ODM\Field(type="raw", name="template")
     */
    private $template;

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
     * @ODM\ReferenceMany(targetDocument="App", inversedBy="type")
     * @JMS\Groups({"full"})
     */
    public $apps;


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
     * @param string
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
     * @param string
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
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set template
     *
     * @return ApplicationType
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
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
     * @param string
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
}
