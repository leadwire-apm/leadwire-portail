<?php declare (strict_types = 1);

namespace AppBundle\Document;

use AppBundle\Document\ApplicationType;
use AppBundle\Document\MonitoringSet;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\Document(repositoryClass="AppBundle\Repository\TemplateRepository")
 * @ODM\UniqueIndex(keys={"name"="asc", "version"="desc", "monitoringSet"="asc"})
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 */
class Template
{
    const DASHBOARDS = "Dashboards";
    const DASHBAORDS_ALL = "Dashboards-All";
    const INDEX_TEMPLATE = "Index-Template";
    const INDEX_PATTERN = "Index-Pattern";

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
    private $content;

    /**
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     *
     * @var string
     */
    private $version;

    /**
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     *
     * @var string
     */
    private $type;

    /**
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\ApplicationType",cascade={"persist"}, inversedBy="templates", storeAs="dbRef")
     * @JMS\Expose
     * @JMS\Type("AppBundle\Document\ApplicationType")
     *
     * @var ApplicationType
     */
    private $applicationType;

    /**
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\MonitoringSet",cascade={"persist"}, inversedBy="templates", storeAs="dbRef")
     * @JMS\Expose
     * @JMS\Type("AppBundle\Document\ApplicationType")
     *
     * @var ?MonitoringSet
     */
    private $monitoringSet;

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
     * Get the value of content
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    public function getContentObject(): \stdClass
    {
        return json_decode($this->content, false);
    }

    /**
     * Set the value of content
     *
     * @param  string  $content
     *
     * @return self
     */
    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get the value of version
     *
     * @return  string
     */
    public function getVersion(): string
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
    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get the value of applicationType
     *
     * @return  ApplicationType
     */
    public function getApplicationType(): ApplicationType
    {
        return $this->applicationType;
    }

    /**
     * Set the value of applicationType
     *
     * @param  ApplicationType  $applicationType
     *
     * @return  self
     */
    public function setApplicationType(ApplicationType $applicationType): self
    {
        $this->applicationType = $applicationType;

        return $this;
    }

    /**
     * Get the value of monitoringSet
     *
     * @return  MonitoringSet|null
     */
    public function getMonitoringSet(): ?MonitoringSet
    {
        return $this->monitoringSet;
    }

    /**
     * Set the value of monitoringSet
     *
     * @param  ?MonitoringSet  $monitoringSet
     *
     * @return  self
     */
    public function setMonitoringSet(?MonitoringSet $monitoringSet): self
    {
        $this->monitoringSet = $monitoringSet;

        return $this;
    }

    /**
     * Get the value of type
     *
     * @return  string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @param  string  $type
     *
     * @return  self
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    public static function getTypes()
    {
        return [
            self::DASHBOARDS,
            self::DASHBAORDS_ALL,
            self::INDEX_TEMPLATE,
            self::INDEX_PATTERN,
        ];
    }

    public function getFormattedVersion()
    {
        if ($this->monitoringSet !== null) {
            return strtolower($this->monitoringSet->getQualifier()) . "-" . $this->version;
        }

        return '-';
    }
}
