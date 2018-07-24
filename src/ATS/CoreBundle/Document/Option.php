<?php declare(strict_types=1);

namespace ATS\CoreBundle\Document;

use ATS\CoreBundle\Document\OptionValue;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\Document(collection="Option", repositoryClass="ATS\CoreBundle\Repository\OptionRepository")
 * @ODM\HasLifecycleCallbacks
 * @JMS\ExclusionPolicy("all")
 */
class Option
{
    const STRING_VALUE = "string";
    const NUMERIC_VALUE = "numeric";
    const ARRAY_VALUE = "array";
    const BOOLEAN_VALUE = "bool";

    /**
     * @var \MongoId
     *
     * @ODM\Id("strategy=auto")
     * @JMS\Type("string")
     * @JMS\Expose
     */
    protected $id;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="key")
     * @ODM\UniqueIndex
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    protected $key;

    /**
     * @var OptionValue
     *
     * @ODM\EmbedOne(targetDocument="ATS\CoreBundle\Document\OptionValue", name="value")
     * @JMS\Type("ATS\CoreBundle\Document\OptionValue")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    protected $optionValue;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="type")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $type;

    /**
     * @var bool
     *
     * @ODM\Field(type="boolean", name="enabled")
     * @JMS\Type("boolean")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $enabled;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="description")
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    private $description;

    /**
     * Constructor
     */
    public function __construct($key = '', $value = null, $type = self::STRING_VALUE)
    {
        $this->key = $key;
        $this->type = $type;
        $this->optionValue = null;

        $this->setValue($value);
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
     * Get key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set key
     * @param string $key
     *
     * @return Option
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @JMS\VirtualProperty
     *
     * @return mixed
     */
    public function getValue()
    {
        switch ($this->type) {
            case self::STRING_VALUE:
                return $this->optionValue->getStringValue();
            case self::NUMERIC_VALUE:
                return $this->optionValue->getNumericValue();
            case self::ARRAY_VALUE:
                return $this->optionValue->getArrayValue();
            case self::BOOLEAN_VALUE:
                return $this->optionValue->getBooleanValue();
            default:
                return null;
        }
    }

    /**
     * Set value - Helper method
     *
     * @param string $value
     *
     * @return Option
     */
    public function setValue($value)
    {
        if (!$this->optionValue) {
            $this->optionValue = new OptionValue();
        }

        switch ($this->type) {
            case self::STRING_VALUE:
                $this->optionValue->setStringValue($value);
                return $this;
            case self::NUMERIC_VALUE:
                $this->optionValue->setNumericValue($value);
                return $this;
            case self::ARRAY_VALUE:
                $this->optionValue->setArrayValue($value);
                return $this;
            case self::BOOLEAN_VALUE:
                $this->optionValue->setBooleanValue($value);
                return $this;
            default:
                return $this;
        }
    }

    /**
     * Get type
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     * @param string $type
     *
     * @return Option
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return bool
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set enabled
     *
     * @param bool $enabled
     *
     * @return Option
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Option
     */
    public function setDescription($description)
    {
        $this->description = $description;

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
     * Get the value of optionValue
     *
     * @return  OptionValue
     */
    public function getOptionValue()
    {
        return $this->optionValue;
    }

    /**
     * Set the value of optionValue
     *
     * @param  OptionValue  $optionValue
     *
     * @return  self
     */
    public function setOptionValue(OptionValue $optionValue)
    {
        $this->optionValue = $optionValue;

        return $this;
    }
}
