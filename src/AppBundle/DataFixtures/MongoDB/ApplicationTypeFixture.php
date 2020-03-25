<?php

namespace AppBundle\DataFixtures\MongoDB;

use AppBundle\Document\ApplicationType;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ApplicationTypeFixture extends AbstractFixture implements OrderedFixtureInterface
{
    const DEFAULT_TYPE_REFERENCE = 'default-type';

    public function load(ObjectManager $manager)
    {
        $defaultType = new ApplicationType();
        $defaultType->setName(ApplicationType::DEFAULT_TYPE);
        $defaultType->setDescription("Elastic Stack 7.2.x");
        $defaultType->setVersion(1);

        $manager->persist($defaultType);
        $manager->flush();

        $this->addReference(self::DEFAULT_TYPE_REFERENCE, $defaultType);
    }

    public function getOrder()
    {
        return 0;
    }
}
