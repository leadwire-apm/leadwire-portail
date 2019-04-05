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
        $defaultType->setInstallation((string) file_get_contents("./app/Resources/templates/javaagent/README.md"));
        $defaultType->setAgent("https://github.com/leadwire-apm/leadwire-javaagent");

        $manager->persist($defaultType);
        $manager->flush();

        $this->addReference(self::DEFAULT_TYPE_REFERENCE, $defaultType);
    }

    public function getOrder()
    {
        return 0;
    }
}
