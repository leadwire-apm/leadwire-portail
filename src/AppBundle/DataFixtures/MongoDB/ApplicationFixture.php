<?php

namespace AppBundle\DataFixtures\MongoDB;

use AppBundle\DataFixtures\MongoDB\ApplicationTypeFixture;
use AppBundle\DataFixtures\MongoDB\EnvironmentFixture;
use AppBundle\Document\Application;
use AppBundle\Document\ApplicationType;
use AppBundle\Document\Environment;
use AppBundle\Document\AccessLevel;
use AppBundle\Document\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ApplicationFixture extends AbstractFixture implements OrderedFixtureInterface
{
    const JPETSTORE_APPLICATION = 'jpetstore';

    public function load(ObjectManager $manager)
    {
        /** @var ApplicationType $applicationType */
        $applicationType = $this->getReference(ApplicationTypeFixture::DEFAULT_TYPE_REFERENCE);

        /** @var Environment $environment */
        $environment = $this->getReference(EnvironmentFixture::DEFAULT_ENVIRONMENT);

        $now = new \DateTime();

        $user = new User();
        $user
            ->setUsername("user_jpetstore")
            ->setActive(true)
            ->setRoles([User::DEFAULT_ROLE])
            ->setUuid("jpetstore")
            ->setAvatar('')
            ->setName("user_jpetstore")
            ->setEmailValid(true)
            ->setLocked(false)
            ->setCompany("LeadWire")
            ->setContact("")
            ->setContactPreference("Email")
            ->setEmail("user_jpetstore@leadwire.io");

        $jpetstore = new Application();
        $jpetstore->setUuid("jpetstore") // * UUID has to be hardcoded since it will be used on Kibana and stuff
            ->setName("jpetstore")
            ->setDescription("A web application built on top of MyBatis 3, Spring 3 and Stripes")
            ->setDeployedTypeVersion($applicationType->getVersion())
            ->setEmail("wassim.dhib@leadwire.io")
            ->setEnabled(true)
            ->setCreatedAt($now)
            ->setDemo(true)
            ->setRemoved(false)
            ->setOwner($user)
            ->setType($applicationType)
            ->addEnvironment($environment);
        $manager->persist($jpetstore);
        $manager->flush();

        // set shared dashboard access level to write
        $accessLevelSharedDashboard = new AccessLevel();
        $accessLevelSharedDashboard
            ->setUser($user)
            ->setEnvironment($environment)
            ->setApplication()
            ->setLevel(AccessLevel::SHARED_DASHBOARD_LEVEL)
            ->setAccess(AccessLevel::WRITE_ACCESS)
        ;
        $user->addAccessLevel($accessLevelSharedDashboard);

        // set app dashboard access level to write
        $accessLevelSharedDashboard = new AccessLevel();
        $accessLevelSharedDashboard
            ->setUser($user)
            ->setEnvironment($environment)
            ->setApplication()
            ->setLevel(AccessLevel::APP_DASHBOARD_LEVEL)
            ->setAccess(AccessLevel::WRITE_ACCESS)
        ;
        $user->addAccessLevel($accessLevelSharedDashboard);

        // set app data access level to write
        $accessLevelSharedDashboard = new AccessLevel();
        $accessLevelSharedDashboard
            ->setUser($user)
            ->setEnvironment($environment)
            ->setApplication()
            ->setLevel(AccessLevel::APP_DATA_LEVEL)
            ->setAccess(AccessLevel::WRITE_ACCESS)
        ;
        $user->addAccessLevel($accessLevelSharedDashboard);

        $manager->persist($user);
        $manager->flush();


        $this->addReference(self::JPETSTORE_APPLICATION, $jpetstore);
    }

    public function getOrder()
    {
        return 10;
    }
}
