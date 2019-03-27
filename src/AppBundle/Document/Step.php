<?php declare (strict_types = 1);

namespace AppBundle\Document;

use JMS\Serializer\Annotation as JMS;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use AppBundle\Document\Tmec;

/**
 * @ODM\Document(repositoryClass="AppBundle\Repository\TemplateRepository")
 * @ODM\UniqueIndex(keys={"version"="desc"})
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 */
class Step
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
     * @ODM\Field(type="int")
     * @JMS\Expose
     * @JMS\Type("int")
     *
     * @var int
     */
    private $order;

    /**
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     *
     * @var string
     */
    private $label;

    /**
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     *
     * @var string
     */
    private $comment;

    /**
     * @var bool
     *
     * @ODM\Field(type="bool")
     * @JMS\Type("boolean")
     * @JMS\Expose
     */
    private $waiting;


    /**
     * @var bool
     *
     * @ODM\Field(type="bool")
     * @JMS\Type("boolean")
     * @JMS\Expose
     */
    private $current;

    /**
     * @var bool
     *
     * @ODM\Field(type="bool")
     * @JMS\Type("boolean")
     * @JMS\Expose
     */
    private $completed;

    /**
     * @var Tmec
     *
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\Tmec", name="compagne", cascade={"persist"}, storeAs="dbRef")
     * @JMS\Type("AppBundle\Document\ApplicationType")
     * @JMS\Expose
     * @JMS\Groups({"full", "Default"})
     */
    private $tmec;


    /**
     * Get tmec
     *
     * @return Tmec
     */
    public function getTmec()
    {
        return $this->tmec;
    }

    /**
     * Set tmec
     * @param Tmec $tmec
     *
     * @return Tmec
     */
    public function setTmec(Tmec $tmec)
    {
        $this->tmec = $tmec;

        return $this;
    }


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
     * Set the value of label
     *
     * @param  string  $label
     *
     * @return  self
     */
    public function setLabel(string $label)
    {
        $this->label = $label;

        return $this;
    }
	
    /**
     * Get the value of label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the value of comment
     *
     * @param  string  $comment
     *
     * @return string
     */
    public function setComment(string $comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get the value of comment
     *
     * @return  string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set waiting
     *
     * @param bool $waiting
     *
     * @return self
     */
    public function setWaiting($waiting): self
    {
        $this->waiting = $waiting;

        return $this;
    }

    /**
     * Get the value of waiting
     *
     * @return  bool
     */
    public function getWaiting()
    {
        return $this->waiting;
    }

    /**
     * Set waiting
     *
     * @param bool $current
     *
     * @return self
     */
    public function setCurrent($current): self
    {
        $this->current = $current;

        return $this;
    }

    /**
     * Get the value of current
     *
     * @return  bool
     */
    public function getCurrent()
    {
        return $this->current;
    }

     /**
     * Get the value of order
     *
     * @return  int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * Set the value of order
     *
     * @param  int  $order
     *
     * @return  self
     */
    public function setOrder(int $order): self
    {
        $this->order = $order;

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
}
