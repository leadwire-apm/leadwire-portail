<?php declare (strict_types = 1);

namespace AppBundle\Document;

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
     * @ODM\Field(type="int")
     * @JMS\Expose
     * @JMS\Type("int")
     *
     * @var int
     */
    private $version;

    /**
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\ApplicationType", inversedBy="templates")
     * @JMS\Expose
     * @JMS\Type("AppBundle\Document\ApplicationType")
     * @var ApplicationType
     */
    private $applicationType;

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
     * @return  int
     */
    public function getVersion(): int
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
    public function setVersion(int $version): self
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
