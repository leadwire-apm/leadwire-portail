<?php declare(strict_types=1);

namespace ATS\ScheduledTaskBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use ATS\CoreBundle\Annotation as ATS;

/**
 * @ODM\Document(repositoryClass="ATS\ScheduledTaskBundle\Repository\TaskRepository")
 * @ODM\HasLifecycleCallbacks
 * @JMS\ExclusionPolicy("all")
 */
class Task
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
     * @var string
     *
     * @ODM\Field(type="string", name="name")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $name;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="command")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $command;

    /**
     * @var \DateTime
     *
     * @ODM\Field(type="date", name="createdAt")
     * @JMS\Type("DateTime")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ODM\Field(type="date", name="latestRun")
     * @JMS\Type("DateTime")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $latestRun;

    /**
     * @var bool
     *
     * @ODM\Field(type="boolean", name="active")
     * @JMS\Type("boolean")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $active;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="interval")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $interval;

    /**
     * @var int
     *
     * @ODM\Field(type="int", name="timeout")
     * @JMS\Type("int")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $timeout;

    /**
     * @var array
     *
     * @ODM\Field(type="hash", name="parameters")
     * @JMS\Type("array")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $parameters;


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
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     * @param string
     *
     * @return Task
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get command
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Set command
     * @param string
     *
     * @return Task
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Get createdAt
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdAt
     * @param \DateTime
     *
     * @return Task
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get latestRun
     * @return \DateTime
     */
    public function getlatestRun()
    {
        return $this->latestRun;
    }

    /**
     * Set createdAt
     * @param \DateTime
     *
     * @return Task
     */
    public function setLatestRun($latestRun)
    {
        $this->latestRun = $latestRun;

        return $this;
    }

    /**
     * Get active
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set active
     * @param bool
     *
     * @return Task
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get interval
     * @return string
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * Set interval
     * @param string
     *
     * @return Task
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;

        return $this;
    }

    /**
     * Get the value of timeout
     *
     * @return  int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Set the value of timeout
     *
     * @param  int  $timeout
     *
     * @return  Task
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Get parameters
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Set parameters
     * @param array
     *
     * @return Task
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

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

    /**
     * Returns a string representation of the command line with necessary parameters
     *
     * @return string
     */
    public function getCommandLine()
    {
        if (count($this->parameters) > 0) {
            return $this->command . ' ' . implode(' ', $this->parameters);
        }

        return $this->command;
    }
}
