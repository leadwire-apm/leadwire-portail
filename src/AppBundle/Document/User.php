<?php

namespace AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique;

/**
 * @ODM\Document(repositoryClass="ATS\UserBundle\Repository\UserRepository")
 * @ODM\HasLifecycleCallbacks
 * @JMS\ExclusionPolicy("all")
 * @Unique(fields={"username"})
 * @Unique(fields={"email"})
 */

class User extends \ATS\UserBundle\Document\User
{

    /**
     * @var \MongoId
     *
     * @ODM\Id("strategy=auto")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"full","Default"})
     */
    private $id;


    /**
     * @var string
     *
     * @ODM\Field(type="string", name="uuid")
     * @ODM\Index(unique=true)
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"full","Default"})
     */
    private $uuid;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="avatar")

     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"full","Default"})
     */
    private $avatar;

    /**
     * @var string
     * @JMS\Expose
     * @JMS\Groups({"full","Default"})
     * @JMS\Type("string")
     * @ODM\Field(type="string")
     */
    private $username;

    /**
     * @var string
     * @JMS\Expose
     * @JMS\Groups({"full", "Default"})
     * @JMS\Type("string")
     * @ODM\Field(type="string")
     */
    private $name;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="company")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"full","Default"})
     */
    private $company;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="contact")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"full","Default"})
     */
    private $contact;


    /**
     * @var string
     *
     * @ODM\Field(type="string", name="contactPreference")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"full","Default"})
     */
    private $contactPreference;

    /**
     * @var boolean
     *
     * @ODM\Field(type="string", name="isEmailValid")
     * @JMS\Type("boolean")
     * @JMS\Groups({"full","Default"})
     */
    private $isEmailValid = false;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="email")

     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"full","Default"})
     */
    private $email;

    /**
     * @var boolean
     * @ODM\Field(type="boolean", name="acceptNewsLetter")
     * @JMS\Type("boolean")
     * @JMS\Expose
     * @JMS\Groups({"full","Default"})
     */
    private $acceptNewsLetter;

    /**
     * @ODM\ReferenceMany(targetDocument="Invitation", mappedBy="user")
     * @JMS\Type("array<AppBundle\Document\Invitation>")
     * @JMS\Expose
     * @JMS\Groups({"full", "Default"})
     */
    public $invitations;

    /**
     * @ODM\ReferenceMany(targetDocument="App", mappedBy="owner")
     * @JMS\Groups({"full","Default"})
     */
    public $myApps;


    /**
     * @var App
     *
     * @ODM\ReferenceOne(targetDocument="AppBundle\Document\App", name="defaultApp", cascade={"persist"})
     * @JMS\Type("AppBundle\Document\App")
     * @JMS\Expose
     * @JMS\Groups({"Default", "full"})
     */
    private $defaultApp = null;


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

    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * Set isEmailValid
     *
     * @param bool isEmailValid
     *
     * @return User
     */
    public function setIsEmailValid($isEmailValid)
    {
        $this->isEmailValid = $isEmailValid;

        return $this;
    }

    /**
     * Get isEmailValid
     *
     * @return bool
     */
    public function getIsEmailValid()
    {
        return $this->isEmailValid;
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

    /**
     * @return App
     */
    public function getDefaultApp(): App
    {
        return $this->defaultApp;
    }

    /**
     * @param App $defaultApp
     * @return User
     */
    public function setDefaultApp(App $defaultApp)
    {
        $this->defaultApp = $defaultApp;
        return $this;
    }
}
