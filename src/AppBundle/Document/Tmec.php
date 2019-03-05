<?php declare (strict_types = 1);

namespace AppBundle\Document;

use JMS\Serializer\Annotation as JMS;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

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
     * @var \DateTime
     *
     * @ODM\Field(type="date", name="startDate")
     * @JMS\Type("date")
     * @JMS\Expose
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ODM\Field(type="date", name="endDate")
     * @JMS\Type("date")
     * @JMS\Expose
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
     * Get the value of id
     *
     * @return  \MongoId
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @param  string  $id
     *
     * @return  self
     */
    public function setId(string $id)
    {
        $this->id = $id;

        return $this;
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
     * @return string
     */
    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return Tmec
     */
    public function setStartDate(\DateTime $startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime|null
     */
    public function getStartDate()
    {
        return $this->startDate;
    }
	
	/**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return Tmec
     */
    public function setEndDate(\DateTime $endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime|null
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
        return $this->string;
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
}
