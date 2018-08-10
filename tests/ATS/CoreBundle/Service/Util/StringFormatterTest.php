<?php

namespace Tests\ATS\CoreBundle\Service\Util;

use ATS\CoreBundle\Service\Util\StringFormatter;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class StringFormatterTest extends TestCase
{
    public function testHumanizeDuration()
    {
        $this->assertEquals("0:0:1", StringFormatter::humanizeDuration(1000));
        $this->assertEquals("0:0:10", StringFormatter::humanizeDuration(10000));
        $this->assertEquals("0:1:0", StringFormatter::humanizeDuration(60000));
        $this->assertEquals("1:0:0", StringFormatter::humanizeDuration(3600000));
    }

    public function testHumanizeMemorySize()
    {
        $this->assertEquals('1000B', StringFormatter::humanizeMemorySize(1000));
        $this->assertEquals('1kB', StringFormatter::humanizeMemorySize(1024));
        $this->assertEquals('1MB', StringFormatter::humanizeMemorySize(1048576));
        $this->assertEquals('1GB', StringFormatter::humanizeMemorySize(1073741824));
    }
}
