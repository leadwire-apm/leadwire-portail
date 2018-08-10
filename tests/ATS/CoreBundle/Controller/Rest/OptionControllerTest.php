<?php

namespace Tests\ATS\CoreBundle\Controller\Rest;

use ATS\CoreBundle\Document\Option;
use ATS\CoreBundle\Manager\OptionManager;
use ATS\CoreBundle\Service\Util\StringWrapper;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OptionControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $kernel = self::bootKernel();
        $this->managerRegistry = $kernel->getContainer()->get('doctrine_mongodb');
        $this->documentManager = $kernel->getContainer()
            ->get('doctrine_mongodb')
            ->getManager();

        $this->optionManager = new OptionManager($this->managerRegistry);
        $this->optionManager->deleteAll();

        $this->client = self::createClient([], ['HTTP_HOST' => $kernel->getContainer()->getParameter('app_domain')]);
    }

    public function testGetOptionAction()
    {
        $this->optionManager->deleteAll();
        $option = new Option('getOptionKey', 'testGetOptionAction');
        $this->optionManager->update($option);

        $this->client->request('GET', '/core/api/option/' . $option->getId() . '/get');
        $wsOption = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($wsOption['id'], $option->getId());
    }

    public function testListOptionsAction()
    {
        $this->optionManager->deleteAll();
        $option = new Option('someKey', 'testListOptionsAction');
        $this->optionManager->update($option);
        $anotherOption = new Option('anotherKey', 'random value');
        $this->optionManager->update($anotherOption);

        $this->client->request('GET', '/core/api/option/list');
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $content);
        $this->assertEquals('someKey', $content[0]['key']);
    }

    // public function testNewOptionAction()
    // {
    //     $key = StringWrapper::random(32);
    //     $json = '{"key":"'
    //     .$key.
    //     '","option_value":{"string_value":"Hi there!!","numeric_value":null,
    //     "array_value":null,"boolean_value":null}
    //     ,"type":"string","enabled":true,"description":null}';
    //     $this->client->request(
    //         'POST',
    //         '/core/api/option/new',
    //         [$json],
    //         [],
    //         ['CONTENT_TYPE' => 'application/json']
    //     );
    //     $this->assertTrue($this->client->getResponse()->isSuccessful());
    //     var_dump($this->client->getResponse()->getContent());exit;
    //     // $created = $this->optionManager->getOneBy(['key' => $key]);
    //     $this->assertEquals("Hi there!", $created->getValue());
    // }

    public function testUpdateOptionAction()
    {
        $this->optionManager->deleteAll();
        $option = new Option('someKey', 'testUpdateOptionAction');
        $this->optionManager->update($option);
        $json = json_encode($option->setValue('Some other value'));
        $this->client->request('PUT', '/core/api/option/' . $option->getId() . '/update', [$json]);

        $updated = $this->optionManager->getOneBy(['key' => 'someKey']);

        $this->assertEquals('Some other value', $updated->getValue());
    }

    public function testPaginateRoute()
    {
        $this->optionManager->deleteAll();
        $option = new Option('someKey', 'testListOptionsAction');
        $this->optionManager->update($option);

        $this->client->request('GET', '/core/api/option/paginate');
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $content);
    }

    public function testDeleteOptionAction()
    {
        $this->optionManager->deleteAll();
        $option = new Option('someKey', 'testDeleteOptionAction');
        $this->optionManager->update($option);

        $this->client->request('DELETE', '/core/api/option/' . $option->getId() . '/delete');
        $this->assertCount(0, $this->optionManager->getAll());
    }
}
