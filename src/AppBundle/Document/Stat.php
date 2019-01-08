<?php declare (strict_types = 1);

namespace AppBundle\Document;

use AppBundle\Document\Application;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\Document(repositoryClass="AppBundle\Repository\StatRepository")
 * @ODM\HasLifecycleCallbacks
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 */
class Stat
{
    /**
     * @var \MongoId
     *
     * @ODM\Id("strategy=auto")
     * @JMS\Type("string")
     * @JMS\Expose
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ODM\Field(type="date", name="day")
     * @JMS\Type("DateTime")
     * @JMS\Expose
     * @JMS\Groups({"Default"})
     */
    private $day;

    /**
     * @var integer
     *
     * @ODM\Field(type="integer", name="nbr")
     * @JMS\Type("integer")
     * @JMS\Expose
     * @JMS\Groups({"Default"})
     */
    private $nbr;

    /**
     * @var Application
     *
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\Application", name="app", cascade={"persist"})
     */
    private $application;

    /**
     * Constructor
     */
    public function __construct()
    {
        // auto-generated stub
    }

    /**
     * Get id
     *
     * @return \MongoId
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get day
     *
     * @return \DateTime
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * Set day
     * @param \DateTime $day
     *
     * @return Stat
     */
    public function setDay($day)
    {
        $this->day = $day;

        return $this;
    }

    /**
     * Get nbr
     *
     * @return integer
     */
    public function getNbr()
    {
        return $this->nbr;
    }

    /**
     * Set nbr
     * @param integer $nbr
     *
     * @return Stat
     */
    public function setNbr($nbr)
    {
        $this->nbr = $nbr;

        return $this;
    }

    /**
     * @return Application
     */
    public function getApplication(): Application
    {
        return $this->application;
    }

    /**
     * @param Application $application
     *
     * @return Stat
     */
    public function setApp(Application $application)
    {
        $this->application = $application;

        return $this;
    }
    /**
     * Returns string representation of the object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->id;
    }
}
