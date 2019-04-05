<?php declare (strict_types = 1);

namespace AppBundle\Document;

use AppBundle\Document\Template;
use JMS\Serializer\Annotation as JMS;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(repositoryClass="AppBundle\Repository\MonitoringSetRepository")
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 */
class MonitoringSet
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
    private $qualifier;

    /**
     * @ODM\ReferenceMany(targetDocument="AppBundle\Document\Template", storeAs="dbRef", mappedBy="monitoringSet")
     *
     * @var array
     */
    private $templates;

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
        return $this->templates;
    }

    /**
     * Set the value of templates
     *
     * @param  array  $templates
     *
     * @return  self
     */
    public function setTemplates(array $templates)
    {
        $this->templates = $templates;

        return $this;
    }

    public function __toString()
    {
        return $this->qualifier;
    }
}
