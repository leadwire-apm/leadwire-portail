<?php declare (strict_types = 1);

namespace AppBundle\Document;

use AppBundle\Document\MonitoringSet;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\Document(repositoryClass="AppBundle\Repository\TemplateRepository")
 * @ODM\UniqueIndex(keys={"name"="asc"})
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 */
class Template
{
    const DEFAULT_VERSION = "7.2.1";
    const DASHBOARDS = "Dashboards";
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
     * @JMS\Groups({"Default", "template-list"})
     *
     * @var string
     */
    private $name;

    /**
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Groups({"full"})
     * @JMS\Type("string")
     *
     * @var string
     */
    private $content;

    /**
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     * @JMS\Groups({"Default", "template-list"})
     *
     * @var string
     */
    private $type;

    /**
     * @var ArrayCollection
     * @JMS\Expose
     * @JMS\Groups({"Default", "template-list"})
     */
    private $attachedMonitoringSets;

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
     * Set the value of monitoringSet
     *
     * @param  MonitoringSet  $monitoringSet
     *
     * @return  self
     */
    public function setMonitoringSet(MonitoringSet &$monitoringSet): self
    {
        $monitoringSet->addTemplate($this);

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
            self::INDEX_TEMPLATE,
            self::INDEX_PATTERN,
        ];
    }

    /**
     * Get the value of attachedMonitoringSets
     *
     * @return  ArrayCollection
     */
    public function getAttachedMonitoringSets()
    {
        return $this->attachedMonitoringSets;
    }

    /**
     * Set the value of attachedMonitoringSets
     *
     * @param  ArrayCollection  $attachedMonitoringSets
     *
     * @return  self
     */
    public function setAttachedMonitoringSets(ArrayCollection $attachedMonitoringSets)
    {
        $this->attachedMonitoringSets = $attachedMonitoringSets;

        return $this;
    }
}
