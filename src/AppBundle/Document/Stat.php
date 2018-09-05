<?php declare(strict_types=1);

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use ATS\CoreBundle\Annotation as ATS;

/**
 * @ODM\Document(repositoryClass="AppBundle\Repository\StatRepository")
 * @ODM\HasLifecycleCallbacks
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 * @ATS\ApplicationView
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
     * @JMS\Groups({})
     */
    private $day;

    /**
     * @var integer
     *
     * @ODM\Field(type="integer", name="nbr")
     * @JMS\Type("integer")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $nbr;

    /**
     * @var App
     *
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\App", name="app", cascade={"persist"})
     */
    private $app;


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
     * @param \DateTime
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
     * @param integer
     *
     * @return Stat
     */
    public function setNbr($nbr)
    {
        $this->nbr = $nbr;
        return $this;
    }

    /**
     * @return App
     */
    public function getApp(): App
    {
        return $this->app;
    }

    /**
     * @param App $defaultApp
     * @return Stat
     */
    public function setApp(App $app)
    {
        $this->app = $app;
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
