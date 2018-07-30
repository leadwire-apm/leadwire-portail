<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * @ODM\Document(repositoryClass="ATS\UserBundle\Repository\UserRepository")
 * @ODM\HasLifecycleCallbacks
 * @JMS\ExclusionPolicy("all")
 */

class User extends \ATS\UserBundle\Document\User
{

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="uuid")
     * @ODM\Index(unique=true)
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $uuid;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="avatar")

     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $avatar;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="company")

     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $company;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="contact")
     */
    private $contact;


    /**
     * @var string
     *
     * @ODM\Field(type="string", name="contactPreference")
     */
    private $contactPreference;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="email")

     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $email;

    /**
     * @var boolean
     * @ODM\Field(type="boolean", name="acceptNewsLetter")
     */
    private $acceptNewsLetter;

    /**
     * Set uuid
     *
     * @param string $uuid
     *
     * @return User
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }


    /**
     * Get uuid
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Set avatar
     *
     * @param string $avatar
     *
     * @return User
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Get login
     * @return string
     */
    public function getLogin()
    {
        return $this->getUsername();
    }


    /**
     * Set company
     *
     * @param string $company
     *
     * @return User
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set acceptNewsLetter
     *
     * @param bool $acceptNewsLetter
     *
     * @return User
     */
    public function setAcceptNewsLetter($acceptNewsLetter)
    {
        $this->acceptNewsLetter = $acceptNewsLetter;

        return $this;
    }

    /**
     * Get acceptNewsLetter
     *
     * @return bool
     */
    public function getAcceptNewsLetter()
    {
        return $this->acceptNewsLetter;
    }

    /**
     * Set contact
     *
     * @param string $contact
     *
     * @return User
     */
    public function setContact($contact)
    {
        $this->contact = $contact ;

        return $this;
    }

    /**
     * Get contact
     *
     * @return string
     */
    public function getContact()
    {
        return $this->contact ;
    }

    /**
     * Set contactPreference
     *
     * @param string $contact
     *
     * @return User
     */
    public function setContactPreference($contactPreference)
    {
        $this->contactPreference = $contactPreference ;

        return $this;
    }

    /**
     * Get contactPreference
     *
     * @return string
     */
    public function getContactPreference()
    {
        return $this->contactPreference ;
    }

}
