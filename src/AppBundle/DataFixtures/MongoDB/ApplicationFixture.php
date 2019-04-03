<?php

namespace AppBundle\DataFixtures\MongoDB;

use AppBundle\Document\User;
use AppBundle\Document\Application;
use AppBundle\Document\ApplicationType;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use AppBundle\DataFixtures\MongoDB\ApplicationTypeFixture;

class ApplicationFixture extends AbstractFixture implements OrderedFixtureInterface
{
    const JPETSTORE_APPLICATION = 'jpetstore';

    public function load(ObjectManager $manager)
    {
        /** @var ApplicationType $applicationType */
        $applicationType = $this->getReference(ApplicationTypeFixture::DEFAULT_TYPE_REFERENCE);

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
        $manager->persist($user);

        $jpetstore = new Application();
        $jpetstore->setUuid("jpetstore") // * UUID has to be hardcoded since it will be used on Kibana and stuff
            ->setName("jpetstore")
            ->setDescription("A web application built on top of MyBatis 3, Spring 3 and Stripes")
            ->setEmail("wassim.dhib@leadwire.io")
            ->setEnabled(true)
            ->setDemo(true)
            ->setRemoved(false)
            ->setOwner($user)
            ->setType($applicationType);

        $manager->persist($jpetstore);

        $manager->flush();

        $this->addReference(self::JPETSTORE_APPLICATION, $jpetstore);
    }

    public function getOrder()
    {
        return 10;
    }
}