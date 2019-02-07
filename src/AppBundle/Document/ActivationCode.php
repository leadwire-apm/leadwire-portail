<?php declare (strict_types = 1);

namespace AppBundle\Document;

use AppBundle\Document\Application;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(repositoryClass="AppBundle\Repository\ActivationCodeRepository")
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class ActivationCode
{

    /**
     * @ODM\Id(strategy="auto")
     *
     * @var \MongoId
     */
    private $id;

    /**
     * @ODM\Field(type="string")
     * @ODM\UniqueIndex()
     * @var string
     */
    private $code;

    /**
     * @ODM\Field(type="date")
     *
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @ODM\Field(type="bool")
     *
     * @var boolean
     */
    private $used;

    /**
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\Application")
     *
     * @var ?Application
     */
    private $application;

    public function __construct()
    {
        $this->used = false;
        $this->application = null;
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
     * Get the value of code
     *
     * @return  string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Set the value of code
     *
     * @param  string  $code
     *
     * @return  self
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get the value of createdAt
     *
     * @return  \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set the value of createdAt
     *
     * @param  \DateTime  $createdAt
     *
     * @return  self
     */
    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get the value of used
     *
     * @return  boolean
     */
    public function isUsed(): bool
    {
        return $this->used;
    }

    /**
     * Set the value of used
     *
     * @param  boolean  $used
     *
     * @return  self
     */
    public function setUsed(bool $used): self
    {
        $this->used = $used;

        return $this;
    }

    /**
     * Get the value of application
     *
     * @return  Application
     */
    public function getApplication(): ?Application
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
    public function setApplication(?Application $application): self
    {
        $this->application = $application;

        return $this;
    }
}
