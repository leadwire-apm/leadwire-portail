<?php

namespace AppBundle\DataFixtures\MongoDB;

use AppBundle\Document\MonitoringSet;
use AppBundle\Document\ApplicationType;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use AppBundle\DataFixtures\MongoDB\ApplicationTypeFixture;

class MonitoringSetFixture extends AbstractFixture implements OrderedFixtureInterface
{
    const METRICBEAT_MONITORING_SET = "METRICBEAT";
    const APM_MONITORING_SET = "APM";

    public function load(ObjectManager $manager)
    {
        /** @var ApplicationType $applicationType */
        $applicationType = $this->getReference(ApplicationTypeFixture::DEFAULT_TYPE_REFERENCE);

	/** APM */
        $ms = new MonitoringSet();
        $ms->setName("APM")->setQualifier("APM")->setVersion("7.2.1")->addToApplicationType($applicationType);
        $manager->persist($ms);
        $this->addReference(self::APM_MONITORING_SET, $ms);

	/** METRICBEAT */
        $ms = new MonitoringSet();
        $ms->setName("Metricbeat")->setQualifier("METRICBEAT")->setVersion("7.2.1")->addToApplicationType($applicationType);
        $manager->persist($ms);
        $manager->persist($applicationType);
        $this->addReference(self::METRICBEAT_MONITORING_SET, $ms);
	
	/** FILEBEAT */
	$ms = new MonitoringSet();
        $ms->setName("Filebeat")->setQualifier("FILEBEAT")->setVersion("7.2.1")->addToApplicationType($applicationType);
        $manager->persist($ms);
        $manager->persist($applicationType);
        $this->addReference(self::FILEBEAT_MONITORING_SET, $ms);

        $manager->flush();
    }

    public function getOrder()
    {
        return 25;
    }
}
