<?php

namespace AppBundle\DataFixtures\MongoDB;

use AppBundle\DataFixtures\MongoDB\ApplicationFixture;
use AppBundle\Document\Application;
use AppBundle\Document\Stat;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class StatFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var Application $jpetstore */
        $jpetstore = $this->getReference(ApplicationFixture::JPETSTORE_APPLICATION);

        $now = new \DateTime();
        $date = clone $now;
        $date->sub(new \DateInterval("P365D"));

        for ($i = 0; $i < 365; $i++) {
            $stat = new Stat();
            $stat->setApplication($jpetstore)->setNbr(random_int(0, 10000))->setDay($date);
            $manager->persist($stat);
            $date->add(new \DateInterval("P1D"));
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 20;
    }
}
