<?php

namespace Tests\ATS\CoreBundle\Document;

use ATS\CoreBundle\Document\Option;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class OptionTest extends TestCase
{

    public function testGettersSetters()
    {
        $option = new Option();
        $option->setKey('key')
            ->setType(Option::STRING_VALUE)
            ->setValue('string value')
            ->setEnabled(true)
            ->setDescription('string value description');

        $this->assertEquals('key', $option->getKey());
        $this->assertEquals('string value', $option->getValue());
        $this->assertEquals(Option::STRING_VALUE, $option->getType());
        $this->assertEquals(true, $option->getEnabled());
        $this->assertEquals('string value description', $option->getDescription());

        $option
            ->setType(Option::NUMERIC_VALUE)
            ->setValue(1);

        $this->assertEquals(1, $option->getOptionValue()->getNumericValue());

        $this->assertEquals(1, $option->getValue());
        $this->assertEquals(Option::NUMERIC_VALUE, $option->getType());

        $option
            ->setType(Option::BOOLEAN_VALUE)
            ->setValue(true);

        $this->assertEquals(true, $option->getValue());
        $this->assertEquals(Option::BOOLEAN_VALUE, $option->getType());

        $option
            ->setType(Option::NUMERIC_VALUE)
            ->setValue(1.5);

        $this->assertEquals(1.5, $option->getValue());
        $this->assertEquals(Option::NUMERIC_VALUE, $option->getType());

        $option
            ->setType(Option::NUMERIC_VALUE);
        $this->assertEquals(Option::NUMERIC_VALUE, $option->getType());

        $option
            ->setType(Option::ARRAY_VALUE)
            ->setValue([1, 2, 3]);

        $this->assertEquals([1, 2, 3], $option->getValue());
        $this->assertEquals(Option::ARRAY_VALUE, $option->getType());
    }
}
