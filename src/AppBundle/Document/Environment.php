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
class Environment
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
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * @ODM\ReferenceMany(targetDocument="AppBundle\Document\Application", mappedBy="environment", storeAs="dbRef")
     * @JMS\Type("array<AppBundle\Document\Application>")
     * @JMS\Expose
     */
    private $applications;


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
     * Set the value of name
     *
     * @param  string  $name
     *
     * @return  self
     */
    public function setName(string $named)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the value of applications
     */
    public function getApplications()
    {
        return $this->applications;
    }

    /**
     * Set the value of applications
     *
     * @return  self
     */
    public function setApplications($applications)
    {
        $this->applications = $applications;

        return $this;
    }

}
