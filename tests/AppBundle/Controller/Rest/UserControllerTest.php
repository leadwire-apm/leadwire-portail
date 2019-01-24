<?php

namespace Tests\AppBundle\Controller\Rest;

use AppBundle\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserControllerTest extends WebTestCase
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * @var Client
     */
    private $client;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
        $this->documentManager = $this->container->get('doctrine_mongodb.odm.document_manager');

        $this->documentManager->getSchemaManager()->dropDatabases();
        $this->documentManager->clear();
        $this->documentManager->getSchemaManager()->updateIndexes();
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown()
    {
        $this->documentManager->getSchemaManager()->dropDatabases();
    }

    public function testDeleteUserAction()
    {
        // $user = new User();
        // $user->setUsername("TestUserName");
        // $this->documentManager->persist($user);
        // $this->documentManager->flush();

        // $params = ['id' => $user->getId()];
        // $url = $this->container->get('router')->generate('delete_user', $params);
        // $this->client->request('DELETE', $url, $params);

        // $response = $this->client->getResponse();
        // $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        // $this->assertTrue($response->isSuccessful());
    }
}