<?php declare(strict_types=1);

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use ATS\CoreBundle\Annotation as ATS;

/**
 * @ODM\Document(repositoryClass="AppBundle\Repository\ApplicationTypeRepository")
 * @ODM\HasLifecycleCallbacks
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 * @ATS\ApplicationView
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
     * @JMS\Groups({})
     */
    private $name;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="installation")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $installation;

    /**
     * @ODM\Hash
     *
     * @ODM\Field(type="hash", name="template")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $template = array();

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="agent")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $agent;


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
     * @return hash
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set template
     * @param hash
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
