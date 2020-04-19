<?php declare (strict_types = 1);

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\Document(repositoryClass="AppBundle\Repository\WatcherRepository")
 * @ODM\HasLifecycleCallbacks
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 */
class Watcher
{
    /**
     * @var \MongoId
     *
     * @ODM\Id("strategy=auto")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"full", "minimalist"})
     */
    private $id;

    /**
     * @ODM\Field(type="string")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"full", "minimalist"})
     *
     * @var string
     */
    private $subject;

    /**
     * @ODM\Field(type="int")
     * @JMS\Type("int")
     * @JMS\Expose
     * @JMS\Groups({"full", "minimalist"})
     *
     * @var int
     */
    private $delay;

    /**
     * @ODM\Field(type="string")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"full", "minimalist"})
     *
     * @var string
     */
    private $res;

    /**
     * @ODM\Field(type="string")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"full", "minimalist"})
     *
     * @var string
     */
    private $body;

    /**
     * @ODM\Field(type="string")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"full", "minimalist"})
     *
     * @var string
     */
    private $fromDate;

    /**
     * @ODM\Field(type="string")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"full", "minimalist"})
     *
     * @var string
     */
    private $toDate;

    /**
     * @ODM\Field(type="string")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"full", "minimalist"})
     *
     * @var string
     */
    private $titre;

    /**
     * @ODM\Field(type="string")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"full", "minimalist"})
     *
     * @var string
     */
    private $dashboard;

    /**
     * @ODM\Field(type="string")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"full", "minimalist"})
     *
     * @var string
     */
    private $envId;

    /**
     * @ODM\Field(type="string")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"full", "minimalist"})
     *
     * @var string
     */
    private $schedule;

    /**
     * @ODM\Field(type="string")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"full", "minimalist"})
     *
     * @var string
     */
    private $to;

    /**
     * @ODM\Field(type="string")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"full", "minimalist"})
     *
     * @var string
     */
    private $url;



    /**
     * Get id
     *
     * @return \MongoId
     */
    public function getId()
    {
        return $this->id;
    }


    /*
     * Set the value of subject
     *
     * @param  string  $subject
     *
     * @return  self
     */
    public function setIp(string $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get the value of subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /*
     * Set the value of delay
     *
     * @param  int  $delay
     *
     * @return  self
     */
    public function setDelay(int $delay)
    {
        $this->delay = $delay;

        return $this;
    }

    /**
     * Get the value of delay
     *
     * @return int
     */
    public function getDelay(): int
    {
        return $this->delay;
    }

    /*
     * Set the value of res
     *
     * @param  string  $res
     *
     * @return  self
     */
    public function setRes(string $res)
    {
        $this->res = $res;

        return $this;
    }

    /**
     * Get the value of res
     *
     * @return string
     */
    public function getRes()
    {
        return $this->res;
    }

    /*
     * Set the value of body
     *
     * @param  string  $body
     *
     * @return  self
     */
    public function setBody(string $body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get the value of body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /*
     * Set the value of fromDate
     *
     * @param  string  $fromDate
     *
     * @return  self
     */
    public function setFromDate(string $fromDate)
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    /**
     * Get the value of fromDate
     *
     * @return string
     */
    public function getFromDate()
    {
        return $this->fromDate;
    }

    /*
     * Set the value of toDate
     *
     * @param  string  $toDate
     *
     * @return  self
     */
    public function setToDate(string $toDate)
    {
        $this->toDate = $toDate;

        return $this;
    }

    /**
     * Get the value of toDate
     *
     * @return string
     */
    public function getToDate()
    {
        return $this->toDate;
    }

    /*
     * Set the value of titre
     *
     * @param  string  $titre
     *
     * @return  self
     */
    public function setTitre(string $titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get the value of titre
     *
     * @return string
     */
    public function getTitre()
    {
        return $this->titre;
    }

     /*
     * Set the value of dashboard
     *
     * @param  string  $dashboard
     *
     * @return  self
     */
    public function setDashboard(string $dashboard)
    {
        $this->dashboard = $dashboard;

        return $this;
    }

    /**
     * Get the value of dashboard
     *
     * @return string
     */
    public function getDitre()
    {
        return $this->dashboard;
    }


    /*
     * Set the value of schedule
     *
     * @param  string  $schedule
     *
     * @return  self
     */
    public function setShedule(string $schedule)
    {
        $this->schedule = $schedule;

        return $this;
    }

    /**
     * Get the value of schedule
     *
     * @return string
     */
    public function getShedule()
    {
        return $this->schedule;
    }

     /*
     * Set the value of to
     *
     * @param  string  $to
     *
     * @return  self
     */
    public function setTo(string $to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Get the value of to
     *
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

     /*
     * Set the value of url
     *
     * @param  string  $url
     *
     * @return  self
     */
    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get the value of url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

     /*
     * Set the value of envId
     *
     * @param  string  $envId
     *
     * @return  self
     */
    public function setEnvId(string $envId)
    {
        $this->envId = $envId;

        return $this;
    }

    /**
     * Get the value of envId
     *
     * @return string
     */
    public function getEnvId()
    {
        return $this->envId;
    }
}
