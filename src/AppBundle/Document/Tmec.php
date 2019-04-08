<?php declare (strict_types = 1);

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\Document(repositoryClass="AppBundle\Repository\TemplateRepository")
 * @ODM\UniqueIndex(keys={"version"="desc"})
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 */
class Tmec
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
    private $version;

    /**
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     *
     * @var string
     */
    private $description;

    /**
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     *
     * @var string
     */
    private $startDate;

    /**
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     *
     * @var string
     */
    private $endDate;

    /**
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     *
     * @var string
     */
    private $application;

    /**
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     *
     * @var string
     */
    private $applicationName;

    /**
     * @var bool
     *
     * @ODM\Field(type="bool")
     * @JMS\Type("boolean")
     * @JMS\Expose
     */
    private $completed;

    /**
     * @ODM\ReferenceMany(targetDocument="Step", mappedBy="compagne", storeAs="dbRef", cascade="{persist}")
     * @JMS\Type("array<AppBundle\Document\Step>")
     * @JMS\Expose
     */
    public $steps;

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
     * Get the value of description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @param  string  $description
     *
     * @return self
     */
    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set startDate
     *
     * @param string $startDate
     *
     * @return self
     */
    public function setStartDate(string $startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return string
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param string $endDate
     *
     * @return Tmec
     */
    public function setEndDate(string $endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return string
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Get the value of application
     *
     * @return  string
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set the value of application
     *
     * @param  string  $application
     *
     * @return  self
     */
    public function setApplication(string $application)
    {
        $this->application = $application;

        return $this;
    }

    /**
     * Set completed
     *
     * @param bool $completed
     *
     * @return self
     */
    public function setCompleted($completed): self
    {
        $this->completed = $completed;

        return $this;
    }

    /**
     * Get the value of completed
     *
     * @return  bool
     */
    public function getCompleted()
    {
        return $this->completed;
    }

    /**
     * Get the value of applicationName
     *
     * @return  string
     */
    public function getApplicationName()
    {
        return $this->applicationName;
    }

    /**
     * Set the value of applicationName
     *
     * @param  string  $applicationName
     *
     * @return  self
     */
    public function setApplicationName(string $applicationName)
    {
        $this->applicationName = $applicationName;

        return $this;
    }
}
