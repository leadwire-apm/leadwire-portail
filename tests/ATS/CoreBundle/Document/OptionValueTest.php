<?php

namespace Tests\ATS\CoreBundle\Document;

use ATS\CoreBundle\Document\OptionValue;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class OptionValueTest extends TestCase
{

    public function testGettersSetters()
    {
        $optionValue = new OptionValue();

        $optionValue->setStringValue('string');
        $this->assertEquals('string', $optionValue->getStringValue());

        $optionValue->setNumericValue(1);
        $this->assertEquals(1, $optionValue->getNumericValue());

        $optionValue->setBooleanValue(true);
        $this->assertEquals(true, $optionValue->getBooleanValue());

        $optionValue->setArrayValue([1, 'dis is it']);
        $this->assertEquals([1, 'dis is it'], $optionValue->getArrayValue());

        $optionValue->setNumericValue(.5);
        $this->assertEquals(.5, $optionValue->getNumericValue());
    }
}
