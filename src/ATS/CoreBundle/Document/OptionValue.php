<?php declare(strict_types=1);

namespace ATS\CoreBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\EmbeddedDocument()
 */
class OptionValue
{
    /**
     * @ODM\Field(type="string")
     * @JMS\Type("string")
     *
     * @var string
     */
    private $stringValue;

    /**
     * @ODM\Field(type="float")
     * @JMS\Type("float")
     *
     * @var float
     */
    private $numericValue;

    /**
     * @ODM\Field(type="hash")
     * @JMS\Type("array")
     *
     * @var array
     */
    private $arrayValue;

    /**
     * @ODM\Field(type="boolean")
     * @JMS\Type("boolean")
     *
     * @var boolean
     */
    private $booleanValue;

    /**
     * Get the value of stringValue
     *
     * @return  string
     */
    public function getStringValue()
    {
        return $this->stringValue;
    }

    /**
     * Set the value of stringValue
     *
     * @param  string  $stringValue
     *
     * @return  self
     */
    public function setStringValue($stringValue)
    {
        $this->stringValue = $stringValue;

        return $this;
    }

    /**
     * Get the value of numericValue
     *
     * @return  float
     */
    public function getNumericValue()
    {
        return $this->numericValue;
    }

    /**
     * Set the value of numericValue
     *
     * @param  float  $numericValue
     *
     * @return  self
     */
    public function setNumericValue($numericValue)
    {
        $this->numericValue = $numericValue;

        return $this;
    }

    /**
     * Get the value of arrayValue
     *
     * @return  array
     */
    public function getArrayValue()
    {
        return $this->arrayValue;
    }

    /**
     * Set the value of arrayValue
     *
     * @param  array  $arrayValue
     *
     * @return  self
     */
    public function setArrayValue(array $arrayValue)
    {
        $this->arrayValue = $arrayValue;

        return $this;
    }

    /**
     * Get the value of booleanValue
     *
     * @return  bool
     */
    public function getBooleanValue()
    {
        return $this->booleanValue;
    }

    /**
     * Set the value of booleanValue
     *
     * @param  bool  $booleanValue
     *
     * @return  self
     */
    public function setBooleanValue($booleanValue)
    {
        $this->booleanValue = $booleanValue;

        return $this;
    }
}
