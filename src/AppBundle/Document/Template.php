<?php declare (strict_types = 1);

namespace AppBundle\Document;

use AppBundle\Document\MonitoringSet;
use JMS\Serializer\Annotation as JMS;
use AppBundle\Document\ApplicationType;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(repositoryClass="AppBundle\Repository\TemplateRepository")
 * @ODM\UniqueIndex(keys={"name"="asc", "version"="desc"})
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 */
class Template
{

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
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\ApplicationType",cascade={"persist"}, inversedBy="templates", storeAs="dbRef")
     * @JMS\Expose
     * @JMS\Type("AppBundle\Document\ApplicationType")
     * @var ApplicationType
     */
    private $applicationType;

    /**
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\MonitoringSet",cascade={"persist"}, inversedBy="templates", storeAs="dbRef")
     * @JMS\Expose
     * @JMS\Type("AppBundle\Document\ApplicationType")
     * @var MonitoringSet
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
}
