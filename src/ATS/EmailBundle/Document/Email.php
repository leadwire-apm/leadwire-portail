<?php declare(strict_types=1);

namespace ATS\EmailBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;

/**
 * @ODM\Document(repositoryClass="ATS\EmailBundle\Repository\EmailRepository")
 * @ODM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class Email
{
    /**
     * @var
     * @ODM\Id(strategy="auto")
     * @Expose
     * @Groups({})
     */
    protected $id;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Expose
     * @Groups({})
     */
    protected $subject;

    /**
     * @var array
     * @ODM\Field(type="hash")
     * @Expose
     * @Groups({})
     */
    protected $messageParameters;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Expose
     * @Groups({})
     */
    protected $senderAddress;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Expose
     * @Groups({})
     */
    protected $senderName;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Expose
     * @Groups({})
     */
    protected $recipientAddress;

    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     * @Expose
     * @Groups({})
     */
    protected $sentAt;

    /**
     * @var string
     * @ODM\Field(type="string")
     * @Expose
     * @Groups({})
     */
    protected $template;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->messageParameters = [];
        $this->template = "EmailBundle::empty.html.twig";
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     *
     * @return Email
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return array
     */
    public function getMessageParameters()
    {
        return $this->messageParameters;
    }

    /**
     * @param array $messageParameters
     *
     * @return Email
     */
    public function setMessageParameters($messageParameters)
    {
        $this->messageParameters = $messageParameters;
        return $this;
    }

    /**
     * @return string
     */
    public function getSenderAddress()
    {
        return $this->senderAddress;
    }

    /**
     * @param string $senderAddress
     *
     * @return Email
     */
    public function setSenderAddress($senderAddress)
    {
        $this->senderAddress = $senderAddress;
        return $this;
    }

    /**
     * @return string
     */
    public function getSenderName()
    {
        return $this->senderName;
    }

    /**
     * @param string $senderName
     *
     * @return Email
     */
    public function setSenderName($senderName)
    {
        $this->senderName = $senderName;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecipientAddress()
    {
        return $this->recipientAddress;
    }

    /**
     * @param string $recipientAddress
     *
     * @return Email
     */
    public function setRecipientAddress($recipientAddress)
    {
        $this->recipientAddress = $recipientAddress;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    /**
     * @param \DateTime $sentAt
     *
     * @return Email
     */
    public function setSentAt($sentAt)
    {
        $this->sentAt = $sentAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     *
     * @return Email
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }
}
