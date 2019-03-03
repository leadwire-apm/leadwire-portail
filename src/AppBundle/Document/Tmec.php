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
     * @ODM\Field(type="date")
     * @JMS\Expose
     * 
     * @var \DateTime
     */
    private $startdate;

    /**
     * @ODM\Field(type="date")
     * @JMS\Expose
     * 
     * @var \DateTime
     */
    private $enddate;

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
     * Set startdate
     *
     * @param \DateTime $startdate
     *
     * @return Tmec
     */
    public function setStartDate(\DateTime $startdate)
    {
        $this->startdate = $startdate;

        return $this;
    }

    /**
     * Get startdate
     *
     * @return \DateTime|null
     */
    public function getStartdate()
    {
        return $this->startdate;
    }
	
	/**
     * Set enddate
     *
     * @param \DateTime $enddate
     *
     * @return Tmec
     */
    public function setEndDate(\DateTime $enddate)
    {
        $this->enddate = $enddate;

        return $this;
    }

    /**
     * Get enddate
     *
     * @return \DateTime|null
     */
    public function getEndDate()
    {
        return $this->enddate;
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
