<?php

namespace Tests\ATS\TranslationBundle;

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
    public function testPageIsSuccessful($url, $method = "GET")
    {
        $client = self::createClient([], ['HTTP_HOST' => $this->container->getParameter('app_domain')]);
        $client->request($method, $url);

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function urlProvider()
    {
        return array(
            array('/translation/'),
            array('/core/api/translation/get-available-languages'),
            array('/core/api/translation/list'),
            array('/core/api/translation/get/lang'),
            array('/core/api/translation/id/get'),
            array('/core/api/translation/id/update', 'PUT'),
            array('/core/api/translation/id/delete', 'DELETE'),
            array('/core/api/translation/new', 'POST'),
            // ...
        );
    }
}
