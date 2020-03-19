<?php declare (strict_types = 1);

namespace AppBundle\Document;

use AppBundle\Document\Template;
use JMS\Serializer\Annotation as JMS;
use AppBundle\Document\ApplicationType;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(repositoryClass="AppBundle\Repository\MonitoringSetRepository")
 * @ODM\UniqueIndex(keys={"name"="asc", "version"="asc"})
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 */
class MonitoringSet
{

    const TEMPLATES_COUNT = 3;
    /**
     * @ODM\Id(strategy="auto")
     * @JMS\Expose
     * @JMS\Type("string")
     *
     * @var \MongoId
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
    private $qualifier;

    /**
     * @ODM\ReferenceMany(targetDocument="AppBundle\Document\Template", storeAs="dbRef", strategy="setArray")
     * @JMS\Expose
     * @JMS\Groups({"full", "Default"})
     * @JMS\Type("ArrayCollection<AppBundle\Document\Template>")
     * @var Collection
     */
    private $templates;

    /**
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     * @var string
     */
    private $version;

    /**
     * @JMS\Expose
     * @var array
     */
    private $applicationTypes;

    public function __construct()
    {
        $this->templates = new ArrayCollection();
    }
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
     * Get the value of name
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
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
     * Get the value of qualifier
     *
     * @return  string
     */
    public function getQualifier()
    {
        return $this->qualifier;
    }

    /**
     * Set the value of qualifier
     *
     * @param  string  $qualifier
     *
     * @return  self
     */
    public function setQualifier(string $qualifier)
    {
        $this->qualifier = $qualifier;

        return $this;
    }

    /**
     * Get the value of templates
     *
     * @return  array
     */
    public function getTemplates()
    {
        return $this->templates->toArray();
    }

    /**
     *
     * @param ArrayCollection $templates
     *
     * @return self
     */
    public function setTemplates(ArrayCollection $templates): self
    {
        $this->templates = $templates;

        return $this;
    }

    /**
     * @param Template $template
     *
     * @return self
     */
    public function addTemplate(Template $template): self
    {
        if ($this->templates->contains($template) === false) {
            $this->templates->add($template);
        }

        return $this;
    }

    /**
     * @return self
     */
    public function resetTemplates(): self
    {
        $this->templates->clear();

        return $this;
    }

    /**
     *
     * @param string $type
     *
     * @return Template|null
     */
    public function getTemplateByType(string $type): ?Template
    {
        $template = $this->templates->filter(
            function (Template $template) use ($type) {
                return $template->getType() === $type;
            }
        )->first();

        if ($template === false) {
            return null;
        }
        return $template;
    }

    public function __toString()
    {
        return $this->qualifier;
    }

    /**
     * Get the value of version
     *
     * @return  string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set the value of version
     *
     * @param  string  $version
     *
     * @return  self
     */
    public function setVersion(string $version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Set the value of applicationType
     *
     * @param  ApplicationType  $applicationType
     *
     * @return  self
     */
    public function addToApplicationType(ApplicationType &$applicationType)
    {
        $applicationType->addMonitoringSet($this);

        return $this;
    }

    /**
     * @JMS\VirtualProperty()
     *
     * @return boolean
     */
    public function isValid(): bool
    {
        return $this->templates->count() === self::TEMPLATES_COUNT;
    }

    public function getFormattedVersion(string $envName, string $appName)
    {
        return strtolower($this->getQualifier()) . "-" . $envName . "-" . $appName . "-" .$this->getVersion();    }

    /**
     * Get the value of applicationTypes
     *
     * @return  array
     */
    public function getApplicationTypes(): array
    {
        return $this->applicationTypes;
    }

    /**
     * Set the value of applicationTypes
     *
     * @param  array  $applicationTypes
     *
     * @return  self
     */
    public function setApplicationTypes(array $applicationTypes): self
    {
        $this->applicationTypes = $applicationTypes;

        return $this;
    }
}
