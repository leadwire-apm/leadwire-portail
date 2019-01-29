<?php


namespace Tests\AppBundle\Service;

use AppBundle\Document\User;
use AppBundle\Document\Application;
use Tests\AppBundle\BaseFunctionalTest;
use ATS\CoreBundle\Service\Util\StringWrapper;



class ApplicationServiceTest extends BaseFunctionalTest
{
    public function testActivateApplication()
    {
        $user = new User();
        $user->setUsername('MyAwesomeUSerName');
        $this->documentManager->persist($user);
        $uuid1 = StringWrapper::random(32);
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
            $app->setUuid(StringWrapper::random(32));
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
}