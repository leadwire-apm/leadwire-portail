<?php declare (strict_types = 1);

namespace AppBundle\Document;

use AppBundle\Document\User;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(repositoryClass="AppBundle\Repository\DashboardRepository")
 * @ODM\HasLifecycleCallbacks
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 *
 */
class Dashboard
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
     * @ODM\Field(type="string", name="userId")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"Default"})
     */
    private $userId;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="applicationId")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"Default"})
     */
    private $applicationId;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="dashboardId")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({"Default"})
     */
    private $dashboardId;

    /**
     * @var boolean
     * @JMS\Expose
     * @JMS\Type("boolean")
     * @ODM\Field(type="boolean", name="visible")
     */
    private $visible;


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
     * Get userId
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set userId
     * @param string $userId
     *
     * @return Dashboard
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Get applicationId
     *
     * @return string
     */
    public function getApplicationId()
    {
        return $this->applicationId;
    }

    /**
     * Set applicationId
     * @param string $applicationId
     *
     * @return Dashboard
     */
    public function setApplicationId($applicationId)
    {
        $this->applicationId = $applicationId;
        return $this;
    }

    /**
     * Get dashboardId
     *
     * @return string
     */
    public function getDashboardId()
    {
        return $this->dashboardId;
    }

    /**
     * Set dashboardId
     * @param string $dashboardId
     *
     * @return Dashboard
     */
    public function setDashboardId($dashboardId)
    {
        $this->dashboardId = $dashboardId;
        return $this;
    }

    /**
     * Get visible
     *
     * @return boolean
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * Set visible
     * @param boolean $visible
     *
     * @return Dashboard
     */
    public function setVisible(bool $visible)
    {
        $this->visible = $visible;

        return $this;
    }

}
