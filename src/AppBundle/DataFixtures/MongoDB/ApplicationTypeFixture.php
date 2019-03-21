<?php

namespace AppBundle\DataFixtures\MongoDB;

use AppBundle\Document\ApplicationType;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use GuzzleHttp\Client;

class ApplicationTypeFixture extends AbstractFixture implements OrderedFixtureInterface
{
    const DEFAULT_TYPE_REFERENCE = 'default-type';

    public function load(ObjectManager $manager)
    {
        $client = new Client();
        $url = "https://github.com/leadwire-apm/leadwire-javaagent";
        $response = $client->get($url . "/raw/stable/README.md", ['stream' => true]);
        $defaultType = new ApplicationType();
        $defaultType->setName(ApplicationType::DEFAULT_TYPE);
        $defaultType->setInstallation($response->getBody()->read(10000));
        $defaultType->setAgent($url);

        $manager->persist($defaultType);
        $manager->flush();

        $this->addReference(self::DEFAULT_TYPE_REFERENCE, $defaultType);
    }

    public function getOrder()
    {
        return 0;
    }
}
