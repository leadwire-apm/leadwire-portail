<?php

namespace AppBundle\DataFixtures\MongoDB;

use AppBundle\Document\Environment;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

class EnvironmentFixture extends AbstractFixture implements OrderedFixtureInterface
{
    const DEFAULT_ENVIRONMENT = "default-environment";

    public function load(ObjectManager $manager)
    {
        $env = new Environment();
        $env->setName("test")->setIp("127.0.0.1")->setDefault();
        $manager->persist($env);
        $this->addReference(self::DEFAULT_ENVIRONMENT, $env);

        $manager->flush();
    }

    public function getOrder()
    {
        return 5;
    }
}
