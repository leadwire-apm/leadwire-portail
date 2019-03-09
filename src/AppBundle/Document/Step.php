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
    private $compagne;

    /**
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     *
     * @var string
     */
    private $comment;

    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="waiting")
     * @JMS\Type("boolean")
     * @JMS\Groups({"full","Default"})
     */
    private $waiting;


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
     * Set the value of compagne
     *
     * @param  string  $compagne
     *
     * @return  self
     */
    public function setCompagne(string $compagne)
    {
        $this->compagne = $compagne;

        return $this;
    }

    /**
     * Get the value of compagne
     *
     * @return  string
     */
    public function getCompagne()
    {
        return $this->compagne;
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
     * Get waiting
     *
     * @return bool
     */
    public function isWaiting()
    {
        return $this->waiting;
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
}
