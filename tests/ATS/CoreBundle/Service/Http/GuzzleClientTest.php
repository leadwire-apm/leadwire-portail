<?php

namespace Tests\ATS\CoreBundle\Service\Http;

use ATS\CoreBundle\Service\Http\GuzzleClient;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class GuzzleClientTest extends TestCase
{
    public function testConstructor()
    {
        $client = new GuzzleClient();

        $this->assertEquals("ATS\CoreBundle\Service\Http\GuzzleClient", get_class($client));
    }
}
