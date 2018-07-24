<?php

namespace Tests\ATS\CoreBundle\HTTPFoundation;

use ATS\CoreBundle\HTTPFoundation\CsvResponse;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class CsvResponseTest extends TestCase
{

    public function testConstructorEmpty()
    {
        $response = new CsvResponse();
        $this->assertSame('', $response->getContent());
    }

    public function testConstructorNotEmpty()
    {
        $response = new CsvResponse([['1', '2', '3']]);
        $this->assertSame("1,2,3\n", $response->getContent());
    }
}
