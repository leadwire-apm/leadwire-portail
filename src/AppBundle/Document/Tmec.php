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
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     *
     * @var string
     */
    private $user;

    /**
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     *
     * @var string
     */
    private $userName;


    /**
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     *
     * @var string
     */
    private $cp;

    /**
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     *
     * @var string
     */
    private $cpName;

    /**
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     *
     * @var string
     */
    private $testEnvr;

    /**
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     *
     * @var string
     */
    private $nTir;

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

        /**
     * Get the value of user
     *
     * @return  string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @param  string  $user
     *
     * @return  self
     */
    public function setUser(string $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of cp
     *
     * @return  string
     */
    public function getCp()
    {
        return $this->cp;
    }

    /**
     * Set the value of cp
     *
     * @param  string  $cp
     *
     * @return  self
     */
    public function setCp(string $cp)
    {
        $this->cp = $cp;

        return $this;
    }

    /**
     * Get the value of testEnvr
     *
     * @return  string
     */
    public function getTestEnvr()
    {
        return $this->testEnvr;
    }

    /**
     * Set the value of testEnvr
     *
     * @param  string  $testEnvr
     *
     * @return  self
     */
    public function setTestEnvr(string $testEnvr)
    {
        $this->testEnvr = $testEnvr;

        return $this;
    }

    /**
     * Get the value of nTir
     *
     * @return  string
     */
    public function getnTir()
    {
        return $this->nTir;
    }

    /**
     * Set the value of nTir
     *
     * @param  string  $nTir
     *
     * @return  self
     */
    public function setnTir(string $nTir)
    {
        $this->nTir = $nTir;

        return $this;
    }

    /**
     * Get the value of userName
     *
     * @return  string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * Set the value of userName
     *
     * @param  string  $userName
     *
     * @return  self
     */
    public function setUserName(string $userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * Get the value of cpName
     *
     * @return  string
     */
    public function getCpName()
    {
        return $this->cpName;
    }

    /**
     * Set the value of cpName
     *
     * @param  string  $cpName
     *
     * @return  self
     */
    public function setCpName(string $cpName)
    {
        $this->cpName = $cpName;

        return $this;
    }
}
