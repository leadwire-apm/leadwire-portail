<?php

namespace Tests\ATS\AdminBundle;

use ATS\CoreBundle\Document\Option;
use ATS\CoreBundle\Manager\OptionManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RoutesAvailabilityFunctionalTest extends WebTestCase
{

    private $container;
    private $id;

    public function setUp()
    {
        $kernel = static::bootKernel();
        $this->container = $kernel->getContainer();
        $optionManager = new OptionManager($this->container->get('doctrine_mongodb'));
        $option = new Option("AdminOption");
        $this->id = $optionManager->update($option);
        $this->client = self::createClient([], ['HTTP_HOST' => $this->container->getParameter('app_domain')]);
    }
    /**
     * @dataProvider urlProvider
     */
    public function testPageIsSuccessful($url, $method)
    {
        if (strpos($url, 'edit') !== false) {
            $url .= $this->id;
        }
        $this->client->request($method, $url);
        $this->assertTrue(
            $this->client->getResponse()->isSuccessful() ||
            $this->client->getResponse()->isRedirection()
        );
    }

    public function urlProvider()
    {
        return array(
            array("/admin/", 'GET'),
            array("/admin/list/ats/corebundle/document/option/page/1", 'GET'),
            array("/admin/new/ats/corebundle/document/option/1", 'GET'),
            array("/admin/new/ats/corebundle/document/option/1", 'POST'),
            array("/admin/delete/ats/corebundle/document/option/1", 'GET'),
            array("/admin/edit/ats/corebundle/document/option/", 'GET'),
        );
    }
}
