<?php

namespace AppBundle\DataFixtures\MongoDB;

use AppBundle\Document\User;
use AppBundle\Document\Application;
use AppBundle\Document\ApplicationPermission;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\DataFixtures\MongoDB\UserFixture;
use Doctrine\Common\DataFixtures\AbstractFixture;
use AppBundle\DataFixtures\MongoDB\ApplicationFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

class ApplicationPermissionFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var User $user */
        $user = $this->getReference(UserFixture::USER_ALI);
        /** @var Application $squash */
        $squash = $this->getReference(ApplicationFixture::SQUASH_APPLICATION);
        /** @var Application $jpetstore */
        $jpetstore = $this->getReference(ApplicationFixture::JPETSTORE_APPLICATION);

        $permission = new ApplicationPermission();
        $permission
            ->setApplication($jpetstore)
            ->setUser($user)
            ->setAccess(ApplicationPermission::ACCESS_DEMO);

        $manager->persist($permission);
        $permission = new ApplicationPermission();
        $permission
            ->setApplication($squash)
            ->setUser($user)
            ->setAccess(ApplicationPermission::ACCESS_DEMO);

        $manager->persist($permission);
        $manager->flush();
    }

    public function getOrder()
    {
        return 120;
    }
}
