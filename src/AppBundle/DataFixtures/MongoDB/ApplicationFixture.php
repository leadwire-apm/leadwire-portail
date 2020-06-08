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
    const DEMO_APPLICATION = 'demo';

    public function load(ObjectManager $manager)
    {
        /** @var ApplicationType $applicationType */
        $applicationType = $this->getReference(ApplicationTypeFixture::DEFAULT_TYPE_REFERENCE);

        /** @var Environment $environment */
        $environment = $this->getReference(EnvironmentFixture::DEFAULT_ENVIRONMENT);

        $now = new \DateTime();

        $user = new User();
        $user
            ->setUsername("demo")
            ->setActive(true)
            ->setRoles([User::DEFAULT_ROLE])
            ->setUuid("demo")
            ->setAvatar('')
            ->setName("demo")
            ->setEmailValid(true)
            ->setLocked(false)
            ->setCompany("LEAD WIRE")
            ->setContact("")
            ->setContactPreference("Email")
            ->setEmail("demo@leadwire.io");

        $demo = new Application();
        $demo->setUuid("demo") // * UUID has to be hardcoded since it will be used on Kibana and stuff
            ->setName("demo")
            ->setDescription("A web application built on top of MyBatis 3, Spring 3 and Stripes")
            ->setDeployedTypeVersion($applicationType->getVersion())
            ->setEmail("demo@leadwire.io")
            ->setEnabled(true)
            ->setCreatedAt($now)
            ->setDemo(true)
            ->setRemoved(false)
            ->setOwner($user)
            ->setType($applicationType)
            ->addEnvironment($environment);
        $manager->persist($demo);
        $manager->flush();

        // set app dashboard access level to write
        $accessLevelAppDashboard = new AccessLevel();
        $accessLevelAppDashboard
            ->setUser($user)
            ->setEnvironment($environment)
            ->setApplication($demo)
            ->setLevel(AccessLevel::ACCESS)
            ->setAccess(AccessLevel::ADMIN);
        $user->addAccessLevel($accessLevelAppDashboard);

        $manager->persist($user);
        $manager->flush();


        $this->addReference(self::DEMO_APPLICATION, $demo);
    }

    public function getOrder()
    {
        return 10;
    }
}
