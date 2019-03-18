<?php declare(strict_types=1);

namespace ATS\PaymentBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;


/**
 * @ODM\Document(repositoryClass="ATS\PaymentBundle\Repository\PlanRepository")
 * @ODM\HasLifecycleCallbacks
 * @ODM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @JMS\ExclusionPolicy("all")
 *
 */
class Plan
{
    const CURRENCY_EURO = "eur";
    const CURRENCY_USD = "usd";

    const FREQUENCY_MONTHLY = "month";
    const FREQUENCY_YEARLY = "year";

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
     * @var integer
     *
     * @ODM\Field(type="integer", name="retention")
     * @JMS\Type("integer")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $retention;

    /**
     * @var integer
     *
     * @ODM\Field(type="integer", name="maxTransactionPerDay")
     * @JMS\Type("integer")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $maxTransactionPerDay;

    /**
     * @var float
     *
     * @ODM\Field(type="float", name="price")
     * @JMS\Type("float")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $price;

    /**
     * @var float
     *
     * @ODM\Field(type="float", name="discount")
     * @JMS\Type("float")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $discount;

    /**
     * @var bool
     *
     * @ODM\Field(type="boolean", name="isCreditCard")
     * @JMS\Type("boolean")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $isCreditCard;

    /**
     * @var string
     * @ODM\Field(type="string", name="token")
     */
    private $token;

    /**
     * @ODM\Field(type="string")
     * @JMS\Expose
     * @JMS\Type("string")
     * @var string
     */
    private $stripeId;
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
     * @ODM\Field(type="date", name="updatedAt")
     * @JMS\Type("DateTime")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $updatedAt;

    /**
     * @ODM\EmbedMany(targetDocument="ATS\PaymentBundle\Document\PricingPlan")
     * @JMS\Type("array<ATS\PaymentBundle\Document\PricingPlan>")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $prices = array();

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
     *
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
     * @return Plan
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get retention
     *
     * @return integer
     */
    public function getRetention()
    {
        return $this->retention;
    }

    /**
     * Set retention
     * @param integer
     *
     * @return Plan
     */
    public function setRetention($retention)
    {
        $this->retention = $retention;
        return $this;
    }

    /**
     * Get maxTransactionPerDay
     *
     * @return integer
     */
    public function getMaxTransactionPerDay()
    {
        return $this->maxTransactionPerDay;
    }

    /**
     * Set maxTransactionPerDay
     * @param integer
     *
     * @return Plan
     */
    public function setMaxTransactionPerDay($maxTransactionPerDay)
    {
        $this->maxTransactionPerDay = $maxTransactionPerDay;
        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    public function getYearlyPrice()
    {
        return (($this->getPrice() * 12 * (100 - $this->discount)) / 100);
    }
    /**
     * Set price
     * @param float
     *
     * @return Plan
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get discount
     *
     * @return float
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set discount
     * @param float
     *
     * @return Plan
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Get isCreditCard
     *
     * @return bool
     */
    public function getIsCreditCard()
    {
        return $this->isCreditCard;
    }

    /**
     * Set isCreditCard
     * @param bool
     *
     * @return Plan
     */
    public function setIsCreditCard($isCreditCard)
    {
        $this->isCreditCard = $isCreditCard;

        return $this;
    }

    /**
     * Get token
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set token
     * @param $token
     * @return Plan
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get createdAt
     *
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
     * @return Plan
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set updatedAt
     * @param \DateTime
     *
     * @return Plan
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return array
     */
    public function getPrices()
    {
        return $this->prices;
    }

    public function setPrices($prices)
    {
        $this->prices = $prices;
    }

    /**
     * @param PricingPlan $price
     * @return $this
     */
    public function addPrice(PricingPlan $price)
    {
        $this->prices[] = $price;
        return $this;
    }

    public function getPricingPlan(string $name): ?array
    {
        $filtered = array_filter($this->prices, function($pricingPlan) {$pricingPlan->getName() === $name;});

        if (empty($filtered) === false) {
            return reset($filtered);
        }

        return null;
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
     * Get the value of stripeId
     *
     * @return  string
     */
    public function getStripeId()
    {
        return $this->stripeId;
    }

    /**
     * Set the value of stripeId
     *
     * @param  string  $stripeId
     *
     * @return  self
     */
    public function setStripeId(string $stripeId)
    {
        $this->stripeId = $stripeId;

        return $this;
    }
}
