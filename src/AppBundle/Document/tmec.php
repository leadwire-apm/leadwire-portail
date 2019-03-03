<?php declare (strict_types = 1);

namespace AppBundle\Document;

use JMS\Serializer\Annotation as JMS;
use AppBundle\Document\Application;
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
     * @ODM\Field(type="date")
     */
    private $startdate;

    /**
     * @var \DateTime
     *
     * @ODM\Field(type="date")
     */
    private $endDate;

    /**
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\Application", inversedBy="templates")
     * @JMS\Expose
     * @JMS\Type("AppBundle\Document\Application")
     * @var Application
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
     * @return User
     */
    public function setStartdate(\DateTime $startdate)
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
     * @return User
     */
    public function setEnddate(\DateTime $enddate)
    {
        $this->enddate = $enddate;

        return $this;
    }

    /**
     * Get enddate
     *
     * @return \DateTime|null
     */
    public function getEnddate()
    {
        return $this->enddate;
    }


    /**
     * Get the value of application
     *
     * @return  Application
     */
    public function getApplication(): Application
    {
        return $this->application;
    }

    /**
     * Set the value of application
     *
     * @param  Application  $application
     *
     * @return  self
     */
    public function setApplicationType(Application $application): self
    {
        $this->application = $application;

        return $this;
    }
}
