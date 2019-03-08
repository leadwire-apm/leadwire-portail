<?php

namespace Tests\ATS\CoreBundle\Service\Util;

use ATS\CoreBundle\Service\Util\AString;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class AStringTest extends TestCase
{
    public function testStartsWith()
    {
        $str = "Hello World";
        $wrapper = new AString($str);

        $this->assertEquals(true, $wrapper->startsWith("Hello"));
        $this->assertEquals(true, $wrapper->startsWith("Hello", true));
        $this->assertEquals(false, $wrapper->startsWith("hello", false));
        $this->assertEquals(false, $wrapper->startsWith("Not Hello", true));
        $this->assertEquals(false, $wrapper->startsWith("Not Hello", false));
    }

    public function testEndsWith()
    {
        $str = "Hello World";
        $wrapper = new AString($str);

        $this->assertEquals(true, $wrapper->endsWith("World"));
        $this->assertEquals(true, $wrapper->endsWith(" World", true));
        $this->assertEquals(false, $wrapper->endsWith("world", false));
        $this->assertEquals(false, $wrapper->endsWith("gibberish", true));
        $this->assertEquals(false, $wrapper->endsWith("gibberish", false));
    }

    public function testContains()
    {
        $str = "Hello World";
        $wrapper = new AString($str);

        $this->assertEquals(true, $wrapper->contains("lo Wo"));
        $this->assertEquals(true, $wrapper->contains("lo Wo", true));
        $this->assertEquals(true, $wrapper->contains("lo Wo", false));
        $this->assertEquals(false, $wrapper->contains(" lo Wo ", true));
        $this->assertEquals(false, $wrapper->contains(" lo Wo ", false));
    }

    public function testStringify()
    {
        $this->assertEquals('True', AString::stringify(true));
        $this->assertEquals('False', AString::stringify(false));
        $this->assertEquals('1|2|3', AString::stringify([1, 2, 3]));
        $this->assertEquals('', AString::stringify([]));
        $this->assertEquals('2001-01-01', AString::stringify((new \DateTime('2001-01-01'))));
    }

    public function testRandom()
    {
        $this->assertEquals(32, strlen(AString::random(32)));
    }
}
