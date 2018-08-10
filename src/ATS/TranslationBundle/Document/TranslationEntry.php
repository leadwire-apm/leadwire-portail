<?php declare(strict_types=1);

namespace ATS\TranslationBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ODM\Document(collection="Translation",
 * repositoryClass="ATS\TranslationBundle\Repository\TranslationEntryRepository")
 * @ODM\HasLifecycleCallbacks
 */
class TranslationEntry
{

    /**
     * @var \MongoId
     * @ODM\Id("strategy=auto")
     * @JMS\Type("string")
     * @JMS\Expose
     */
    protected $id;

    /**
     * @var string
     * @ODM\Field(type="string", name="key")
     * @ODM\UniqueIndex
     * @JMS\Type("string")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    protected $key;

    /**
     * @var array
     * @ODM\Field(type="hash", name="values")
     * @JMS\Type("array")
     * @JMS\Expose
     * @JMS\Groups({})
     */
    protected $values;

    public function __construct($key = '', $values = [])
    {
        $this->key = $key;
        $this->values = $values;
    }

    /**
     * Get the value of id
     *
     * @return  \MongoId
     */
    public function getId()
    {
        return $this->id;
    }

    public function getValueForLanguage($language)
    {
        if (array_key_exists($language, $this->getValues())) {
            return $this->getValues()[$language];
        }

        return null;
    }

    /**
     * Get the value of key
     *
     * @return  string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set the value of key
     *
     * @param  string  $key
     *
     * @return  self
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get the value of values
     *
     * @return  array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Set the value of values
     *
     * @param  array  $values
     *
     * @return  self
     */
    public function setValues(array $values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * Adds a new (language, value) pair
     *
     * @param string $language
     * @param string $value
     * @return void
     */
    public function addValue($language, $value = null)
    {
        if (!array_key_exists($language, $this->values)) {
            $this->values[$language] = $value;
        }
    }
}
