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
    const FILEBEAT_MONITORING_SET = "FILEBEAT";
    const PACKETBEAT_MONITORING_SET = "PACKETBEAT";
    const HEARTBEAT_MONITORING_SET = "HEARTBEAT";    
    const OTEL_SVCMAP_MONITORING_SET = "OTEL-V1-APM-SERVICE-MAP";    
    const OTEL_SPAN_MONITORING_SET = "OTEL-V1-APM-SPAN";    


    public function load(ObjectManager $manager)
    {
        /** @var ApplicationType $applicationType */
        $applicationType = $this->getReference(ApplicationTypeFixture::DEFAULT_TYPE_REFERENCE);

	/** APM */
        $ms = new MonitoringSet();
        $ms->setName("APM")->setQualifier("APM")->setVersion("7.10.0")->addToApplicationType($applicationType);
        $manager->persist($ms);
        $this->addReference(self::APM_MONITORING_SET, $ms);

	/** METRICBEAT */
        $ms = new MonitoringSet();
        $ms->setName("Metricbeat")->setQualifier("METRICBEAT")->setVersion("7.10.0")->addToApplicationType($applicationType);
        $manager->persist($ms);
        $manager->persist($applicationType);
        $this->addReference(self::METRICBEAT_MONITORING_SET, $ms);
	
	/** FILEBEAT */
	$ms = new MonitoringSet();
        $ms->setName("Filebeat")->setQualifier("FILEBEAT")->setVersion("7.10.0")->addToApplicationType($applicationType);
        $manager->persist($ms);
        $manager->persist($applicationType);
        $this->addReference(self::FILEBEAT_MONITORING_SET, $ms);

      /** PACKETBEAT */
        $ms = new MonitoringSet();
        $ms->setName("Packetbeat")->setQualifier("PACKETBEAT")->setVersion("7.10.0")->addToApplicationType($applicationType);
        $manager->persist($ms);
        $manager->persist($applicationType);
        $this->addReference(self::PACKETBEAT_MONITORING_SET, $ms);

      /** HEARTBEAT */
        $ms = new MonitoringSet();
        $ms->setName("Heartbeat")->setQualifier("HEARTBEAT")->setVersion("7.10.0")->addToApplicationType($applicationType);
        $manager->persist($ms);
        $manager->persist($applicationType);
        $this->addReference(self::HEARTBEAT_MONITORING_SET, $ms);
	    
      /** OTEL-V1-APM-SERVICE-MAP */
        $ms = new MonitoringSet();
        $ms->setName("OTEL-V1-APM-SERVICE-MAP")->setQualifier("OTEL-V1-APM-SERVICE-MAP")->setVersion("7.10.0")->addToApplicationType($applicationType);
        $manager->persist($ms);
        $manager->persist($applicationType);
        $this->addReference(self::OTEL_SVCMAP_MONITORING_SET, $ms);
	    
      /** OTEL-V1-APM-SPAN */
        $ms = new MonitoringSet();
        $ms->setName("OTEL-V1-APM-SPAN")->setQualifier("OTEL-V1-APM-SPAN")->setVersion("7.10.0")->addToApplicationType($applicationType);
        $manager->persist($ms);
        $manager->persist($applicationType);
        $this->addReference(self::OTEL_SPAN_MONITORING_SET, $ms);


        $manager->flush();
    }

    public function getOrder()
    {
        return 25;
    }
}
