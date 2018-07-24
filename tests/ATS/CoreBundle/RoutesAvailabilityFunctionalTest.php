<?php

namespace Tests\ATS\CoreBundle;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RoutesAvailabilityFunctionalTest extends WebTestCase
{

    private $container;

    public function setUp()
    {
        $kernel = static::bootKernel();
        $this->container = $kernel->getContainer();
    }
    /**
     * @dataProvider urlProvider
     */
    public function testPageIsSuccessful($url, $method)
    {
        $client = self::createClient([], ['HTTP_HOST' => $this->container->getParameter('app_domain')]);
        $client->request($method, $url);
        // var_dump($url);exit;
        // var_dump($client->getResponse());exit;
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function urlProvider()
    {
        return array(
            // array('/core/api/upload', 'POST'),
            array('/client/__context', 'GET'),
        );
    }
}
