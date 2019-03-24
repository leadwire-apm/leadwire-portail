<?php


namespace Tests\AppBundle\Service;

use AppBundle\Document\User;
use AppBundle\Document\Application;
use AppBundle\Document\ActivationCode;
use Tests\AppBundle\BaseFunctionalTest;
use AppBundle\Manager\ApplicationManager;
use ATS\CoreBundle\Service\Util\AString;



class ApplicationServiceTest extends BaseFunctionalTest
{
    public function testActivateApplication()
    {
        $user = new User();
        $user->setUsername('MyAwesomeUSerName');
        $this->documentManager->persist($user);
        $uuid1 = AString::random(32);
        $app = new Application();
        $app
            ->setOwner($user)
            ->setEnabled(false)
            ->setUuid($uuid1)
            ->setRemoved(false);

        $this->documentManager->persist($app);
        $this->documentManager->flush();

        $activatedApp = $this->applicationService->activateApplication($app->getId(), "someCode");
        $this->assertEquals(null, $activatedApp);

        $activatedApp = $this->applicationService->activateApplication($app->getId(), "XBXX7x");
        $this->assertEquals(null, $activatedApp);

        $activationCode = new ActivationCode();
        $activationCode->setCode("XBXX7X");
        $activationCode->setUsed(false);
        $this->documentManager->persist($activationCode);
        $this->documentManager->flush();
        $activatedApp = $this->applicationService->activateApplication($app->getId(), "XBXX7X");
        $this->assertTrue($activatedApp->isEnabled());
    }

    public function testListOwnedApplications()
    {
        $user = new User();
        $user->setUsername("testListOwnedApplications");
        $this->documentManager->persist($user);
        $this->documentManager->flush();

        for ($i = 0; $i < 10; $i++) {
            $app = new Application();
            $app->setUuid(AString::random(32));
            $app->setName("app$i");
            $app->setRemoved(false);
            if ($i === 0) {
                $app->setOwner($user);
            }

            $this->documentManager->persist($app);
        }

        $this->documentManager->flush();

        $myApps = $this->applicationService->listOwnedApplications($user);

        $this->assertCount(1, $myApps);
        $this->assertEquals('app0', $myApps[0]->getName());
    }

    public function testDeleteApplication()
    {
        $am = $this->container->get(ApplicationManager::class);
        $am->deleteAll();
        $this->assertCount(0, $am->getAll());

        $app = new Application();
        $app->setName("app_name")->setUuid(AString::random(32));
        $this->documentManager->persist($app);
        $this->documentManager->flush();
        $this->assertCount(1, $am->getAll());
        $this->applicationService->deleteApplication($app->getId());
        $this->assertCount(1, $am->getAll());

        $this->expectException('Symfony\Component\HttpKernel\Exception\HttpException');
        $this->applicationService->deleteApplication("someInvalidId");
    }

    public function testToggleActivation()
    {
        $app = new Application();
        $app->setName("app_name")->setUuid(AString::random(32));
        $this->documentManager->persist($app);
        $this->documentManager->flush();

        $this->assertFalse($app->isEnabled());

        $success = $this->applicationService->toggleActivation($app->getId());

        $this->assertTrue($success);
        $dbApp = $this->applicationService->getApplication($app->getId());

        $this->assertTrue($dbApp->isEnabled());
    }
}