<?php

namespace AppBundle\DataFixtures\MongoDB;

use AppBundle\Document\MonitoringSet;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class MonitoringSetFixture extends AbstractFixture implements OrderedFixtureInterface
{
    const METRICBEAT_MONITORING_SET = "METRICBEAT";
    const APM_MONITORING_SET = "APM";

    public function load(ObjectManager $manager)
    {
        $ms = new MonitoringSet();
        $ms->setName("APM")->setQualifier("APM");
        $manager->persist($ms);
        $this->addReference(self::APM_MONITORING_SET, $ms);
        $ms = new MonitoringSet();
        $ms->setName("Metricbeat")->setQualifier("METRICBEAT");
        $manager->persist($ms);
        $this->addReference(self::METRICBEAT_MONITORING_SET, $ms);

        $manager->flush();
    }

    public function getOrder()
    {
        return 25;
    }
}
