<?php declare (strict_types = 1);

namespace AppBundle\Document;

use AppBundle\Document\User;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\Document(repositoryClass="AppBundle\Repository\ProcessRepository")
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 */
class Process
{
    const STATUS_IN_PROGRESS = 'in-progress';
    const STATUS_SUCCEEDED = 'succeeded';
    const STATUS_FAILED = 'failed';

    /**
     * @ODM\Id(strategy="auto")
     * @JMS\Expose
     * @JMS\Type("string")
     *
     * @var \MongoId
     */
    private $id;

    /**
     * @var bool
     *
     * @ODM\Field(type="string")
     * @JMS\Type("string")
     * @JMS\Expose
     */
    private $status;

    /**
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     *
     * @var string
     */
    private $message;

    /**
     * @var User
     *
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\User", storeAs="dbRef")
     * @JMS\Type("AppBundle\Document\User")
     * @JMS\Expose
     */
    private $user;

    /**
     * @var string
     *
     * @ODM\Field(type="bool")
     * @JMS\Expose
     * @JMS\Type("bool")
     */
    private $isNewLogin;

    /**
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     *
     * @var string
     */
    private $date;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->status = self::STATUS_IN_PROGRESS;
        $this->date = (new \DateTime('now'))->format('Y-m-s H:i:s');
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
     * Set status
     *
     * @param string $status
     *
     * @return Process
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return Process
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return Process
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get isNewLogin
     *
     * @return bool
     */
    public function getisNewLogin()
    {
        return $this->isNewLogin;
    }

    /**
     * Set isNewLogin
     *
     * @param bool $isNewLogin
     *
     * @return Process
     */
    public function setisNewLogin($isNewLogin)
    {
        $this->isNewLogin = $isNewLogin;

        return $this;
    }

    /**
     * Get the value of date
     *
     * @return  string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set  date
     *
     * @param string $date
     *
     * @return Process
     */
    public function setDate(string $date)
    {
        $this->date = $date;

        return $this;
    }
}
