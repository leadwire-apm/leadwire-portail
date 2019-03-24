<?php

namespace Tests\ATS\CoreBundle\Manager;

use AppBundle\Document\User;
use AppBundle\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AbstractManagerTest extends KernelTestCase
{
    private $documentManager;

    private $managerRegistry;

    /**
     * @var UserManager
     */
    private $userManager;

    protected function setUp()
    {
        $kernel = self::bootKernel();
        $this->managerRegistry = $kernel->getContainer()->get('doctrine_mongodb');
        $this->documentManager = $kernel->getContainer()
            ->get('doctrine_mongodb')
            ->getManager();

        $this->userManager = new UserManager($this->managerRegistry);
    }

    /**
     * @uses ATS\CoreBundle\Repository\BaseDocumentRepository::deleteAll
     *
     * @return void
     */
    public function testPaginate()
    {
        $this->userManager->deleteAll();
        for ($i = 0; $i < 50; $i++) {
            $user = new User("user$i", "user$i@test.com");
            if ($i < 10) {
                $user->setActive(true);
            }
            $this->documentManager->persist($user);
        }
        $this->documentManager->flush();

        $pageItems = $this->userManager->paginate(['active' => true]);
        $this->assertCount(10, $pageItems);
        $pageItems = $this->userManager->paginate();
        $this->assertCount(20, $pageItems);
        $pageItems = $this->userManager->paginate([], 1);
        $this->assertCount(20, $pageItems);
    }

    /**
     * @uses ATS\CoreBundle\Repository\BaseDocumentRepository::deleteAll
     * @uses ATS\CoreBundle\Repository\BaseDocumentRepository::save
     * @return void
     */
    public function testUpdate()
    {
        $this->userManager->deleteAll();

        $user = new User();
        $user->setUsername('toto');
        $this->userManager->update($user);
        $fetched = $this->documentManager->getRepository(User::class)->findBy([]);
        $this->assertCount(1, $fetched);
        $this->assertEquals('toto', $fetched[0]->getUsername());
    }

    /**
     * @uses ATS\CoreBundle\Repository\BaseDocumentRepository::delete
     * @uses ATS\CoreBundle\Repository\BaseDocumentRepository::save
     * @uses ATS\CoreBundle\Repository\BaseDocumentRepository::deleteAll
     *
     * @return void
     */
    public function testDelete()
    {
        $this->userManager->deleteAll();
        $this->assertCount(0, $this->documentManager->getRepository(User::class)->findBy([]));

        $user = new User();
        $user->setUsername('testDelete');
        $this->userManager->update($user);
        $userId = $user->getId();
        $this->assertCount(1, $this->documentManager->getRepository(User::class)->findBy([]));

        $id = $this->userManager->delete($user);

        $this->assertCount(0, $this->documentManager->getRepository(User::class)->findBy([]));
        $this->assertEquals($id, $userId);
    }

    /**
     * @uses ATS\CoreBundle\Repository\BaseDocumentRepository::save
     * @uses ATS\CoreBundle\Repository\BaseDocumentRepository::deleteAll
     * @uses ATS\CoreBundle\Repository\BaseDocumentRepository::delete
     *
     * @return void
     */
    public function testDeleteById()
    {
        $this->userManager->deleteAll();
        $user = new User();
        $user->setUsername('deleteById');
        $this->userManager->update($user);
        $this->assertCount(1, $this->documentManager->getRepository(User::class)->findBy([]));
        $this->userManager->deleteById($user->getId());
        $this->assertCount(0, $this->documentManager->getRepository(User::class)->findBy([]));
    }

    /**
     * @uses ATS\CoreBundle\Repository\BaseDocumentRepository::save
     * @uses ATS\CoreBundle\Repository\BaseDocumentRepository::deleteAll
     *
     * @return void
     */
    public function testDeleteAll()
    {
        $this->userManager->deleteAll();
        $this->assertCount(0, $this->documentManager->getRepository(User::class)->findBy([]));
        $user = new User('deleteAll', 'deleteAll@deleteAll.com');
        $this->userManager->update($user);
        $user = new User('deleteAll2', 'deleteAll2@deleteAll.com');
        $this->userManager->update($user);
        $this->assertCount(2, $this->documentManager->getRepository(User::class)->findBy([]));
        $this->userManager->deleteAll();
        $this->assertCount(0, $this->documentManager->getRepository(User::class)->findBy([]));
    }

    /**
     * @uses ATS\CoreBundle\Repository\BaseDocumentRepository::save
     * @uses ATS\CoreBundle\Repository\BaseDocumentRepository::deleteAll
     *
     * @return void
     */
    public function testGetAll()
    {
        $this->userManager->deleteAll();
        $user = new User('getAll', 'getAll@test.com');
        $user->setUsername('getAll');
        $this->userManager->update($user);
        $this->assertCount(1, $this->userManager->getAll());
        $user = new User('getAll2', 'getAll2@test.com');
        $user->setUsername('getAll2');
        $this->userManager->update($user);
        $this->assertCount(2, $this->userManager->getAll());
        $this->assertEquals("getAll", $this->userManager->getAll()[0]->getUsername());
        $this->assertEquals("getAll2", $this->userManager->getAll()[1]->getUsername());
    }

    /**
     * @uses ATS\CoreBundle\Repository\BaseDocumentRepository::save
     * @uses ATS\CoreBundle\Repository\BaseDocumentRepository::deleteAll
     *
     * @return void
     */
    public function testGetBy()
    {
        $this->userManager->deleteAll();
        $user = new User();
        $user->setUsername('getBy');
        $this->userManager->update($user);
        $users = $this->userManager->getBy(['username' => "getBy"]);
        $this->assertCount(1, $users);
    }

    /**
     * @uses ATS\CoreBundle\Repository\BaseDocumentRepository::save
     * @uses ATS\CoreBundle\Repository\BaseDocumentRepository::deleteAll
     *
     * @return void
     */
    public function testGetOneBy()
    {
        $this->userManager->deleteAll();
        $user = new User('user1', 'user1');
        $user->setActive(true);
        $this->userManager->update($user);
        $user = new User('user2', 'user2');
        $user->setActive(true);
        $this->userManager->update($user);
        $user = new User('user3', 'user3');
        $user->setUsername('user3');
        $user->setActive(false);
        $this->userManager->update($user);
        $users = $this->userManager->getBy(['active' => true]);
        $this->assertCount(2, $users);
        $users = $this->userManager->getBy(['active' => false]);
        $this->assertCount(1, $users);
        $this->assertEquals('user3', $users[0]->getUsername());
    }


    /**
     * @uses ATS\CoreBundle\Repository\BaseDocumentRepository::deleteAll
     *
     * @return void
     */
    public function testBatchUpdate()
    {
        $this->userManager->deleteAll();
        $users = [];
        for ($i = 0; $i < 50; $i++) {
            $users[] = new User("user$i", "user$i@test.com");
        }

        $this->assertCount(50, $users);
        $this->userManager->batchUpdate($users);

        $fetched = $this->userManager->getAll();
        $this->assertCount(50, $fetched);

    }
    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->documentManager->close();
        $this->documentManager = null;
    }
}
