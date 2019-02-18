<?php declare (strict_types = 1);

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

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
     *
     * @var \MongoId
     */
    private $id;

    /**
     * @ODM\Field(type="string")
     * @JMS\Expose
     *
     * @var string
     */
    private $name;

    /**
     * @ODM\Field(type="string")
     * @JMS\Expose
     *
     * @var string
     */
    private $content;

    /**
     * @ODM\Field(type="int")
     * @JMS\Expose
     *
     * @var int
     */
    private $version;

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
     * @return  string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set the value of content
     *
     * @param  string  $content
     *
     * @return  self
     */
    public function setContent(string $content)
    {
        $this->content = $content;

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
}
