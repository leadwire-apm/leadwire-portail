<?php declare (strict_types = 1);

namespace ATS\UserBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * @ODM\Document(repositoryClass="ATS\UserBundle\Repository\UserRepository")
 * @ODM\HasLifecycleCallbacks
 * @JMS\ExclusionPolicy("all")
 */
class User implements AdvancedUserInterface, \Serializable
{
    const DEFAULT_ROLE = "ROLE_USER";

    /**
     * @var \MongoId
     *
     * @ODM\Id("strategy=auto")
     * @JMS\Expose
     * @JMS\Type("string")
     */
    protected $id;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     */
    protected $username;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $password;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $salt;

    /**
     * @var bool
     *
     * @ODM\Field(type="boolean")
     * @JMS\Expose
     */
    private $active;

    /**
     * @var \DateTime
     *
     * @ODM\Field(type="date")
     */
    private $expireAt;

    /**
     * @var array
     *
     * @ODM\Field(type="hash")
     * @JMS\Expose
     */
    private $roles;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    private $apiKey;

    public function __construct()
    {
        $this->roles = [];
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
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
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

    /**
     * Set salt
     *
     * @param string $salt
     *
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set active
     *
     * @param string $active
     *
     * @return User
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return string
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Activates a user
     *
     * @return User
     */
    public function activate()
    {
        return $this->setActive(true);
    }

    /**
     * Deactivates a user
     *
     * @return User
     */
    public function deactivate()
    {
        return $this->setActive(false);
    }

    /**
     * Set expireAt
     *
     * @param \DateTime $expireAt
     *
     * @return User
     */
    public function setExpireAt(\DateTime $expireAt)
    {
        $this->expireAt = $expireAt;

        return $this;
    }

    /**
     * Get expireAt
     *
     * @return \DateTime
     */
    public function getExpireAt()
    {
        return $this->expireAt;
    }

    /**
     * Set roles
     *
     * @param array $roles
     *
     * @return User
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {

        if (!in_array(self::DEFAULT_ROLE, $this->roles)) {
            array_push($this->roles, self::DEFAULT_ROLE);
        }

        return $this->roles;
    }

    /**
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {
        return in_array($role, $this->roles);
    }

    /**
     * Promote user roles
     *
     * @param string $role
     *
     * @return User
     */
    public function promote($role)
    {
        if (!in_array($role, $this->roles)) {
            array_push($this->roles, $role);
        }

        return $this;
    }

    /**
     * Revoke user roles
     *
     * @param string $role
     *
     * @return User
     */
    public function revoke($role)
    {
        if (null == $this->roles) {
            $this->roles = [];
        }

        $this->roles = array_diff($this->roles, [$role]);

        return $this;
    }

    /**
     * Set apiKey
     *
     * @param string $apiKey
     *
     * @return User
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Get apiKey
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * {@inheritDoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function isAccountNonExpired()
    {
        if ($this->getExpireAt() == null) {
            return true;
        }

        return $this->getExpireAt() > (new \DateTime());
    }

    /**
     * {@inheritDoc}
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @JMS\VirtualProperty()
     */
    public function isEnabled()
    {
        return $this->active;
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
            $this->salt,
            $this->active,
        ]);
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->username,
            $this->password,
            $this->salt,
            $this->active
        ) = unserialize($serialized);
    }

    public function __toString()
    {
        if ($this->username != null) {
            return $this->username;
        }

        return $this->id;
    }
}
