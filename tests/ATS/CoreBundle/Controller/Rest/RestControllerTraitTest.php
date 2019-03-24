<?php

namespace Tests\ATS\CoreBundle\Controller\Rest;

use AppBundle\Document\User;
use AppBundle\Manager\UserManager;
use ATS\CoreBundle\Controller\Rest\RestControllerTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\BaseFunctionalTest;

class RestControllerTraitTest extends BaseFunctionalTest
{
    use RestControllerTrait;

    /**
     * @uses ATS\CoreBundle\Manager\AbstractManager::__construct
     * @uses ATS\CoreBundle\Manager\AbstractManager::deleteAll
     * @uses ATS\CoreBundle\Manager\AbstractManager::getAll
     * @uses ATS\CoreBundle\Manager\AbstractManager::getDocumentRepository
     * @uses ATS\CoreBundle\Repository\BaseDocumentRepository::deleteAll
     */
    public function testRenderResponse()
    {
        $kernel = self::bootKernel();
        $managerRegistry = $kernel->getContainer()->get('doctrine_mongodb');
        $this->container = $kernel->getContainer();
        $userManager = new UserManager($managerRegistry);
        $userManager->deleteAll();

        $users = [];
        for ($i = 0; $i < 50; $i++) {
            $user = new User();
            $user->setUsername("user$i");
            $user->setUuid("user$i");
            $managerRegistry->getManager()->persist($user);
        }
        $managerRegistry->getManager()->flush();

        $users = $userManager->getAll();

        $response = $this->renderResponse($users);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $response = $this->renderResponse($users, Response::HTTP_OK, array('undefinedGroup'));

        $this->assertEquals(
            '[{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{}]',
            $response->getContent()
        );
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $response = $this->renderResponse(new \Exception());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }
}
