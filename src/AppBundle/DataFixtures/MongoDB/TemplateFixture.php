<?php

namespace AppBundle\DataFixtures\MongoDB;

use AppBundle\DataFixtures\MongoDB\ApplicationTypeFixture;
use AppBundle\Document\ApplicationType;
use AppBundle\Document\MonitoringSet;
use AppBundle\Document\Template;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Finder\Finder;

class TemplateFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var MonitoringSet $apmMonitoringSet */
        $apmMonitoringSet = $this->getReference(MonitoringSetFixture::APM_MONITORING_SET);
        /** @var MonitoringSet $infrastructureMonitoringSet */
        $infrastructureMonitoringSet = $this->getReference(MonitoringSetFixture::METRICBEAT_MONITORING_SET);
        /** @var MonitoringSet $logMonitoringSet */
        $logMonitoringSet = $this->getReference(MonitoringSetFixture::FILEBEAT_MONITORING_SET);
       /** @var MonitoringSet $networkMonitoringSet */
        $networkMonitoringSet = $this->getReference(MonitoringSetFixture::PACKETBEAT_MONITORING_SET);
       /** @var MonitoringSet $uptimeMonitoringSet */
        $uptimeMonitoringSet = $this->getReference(MonitoringSetFixture::HEARTBEAT_MONITORING_SET);



        $apmFolderPath = "./app/Resources/templates/v7.6.1/apm";
        $finder = new Finder();
        $finder->files()->in($apmFolderPath);
        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            if ($file->getRealPath() === false) {
                throw new \Exception("Error fetching file");
            }
            $template = new Template();
            $template->setName(strtolower($apmMonitoringSet->getName() . "-" . str_replace(".json", "", $file->getFilename())));
            $template->setType(str_replace(".json", "", $file->getFilename()));
            $template->setContent((string) file_get_contents($file->getRealPath()));
            $template->setMonitoringSet($apmMonitoringSet);
            $manager->persist($template);
            $manager->persist($apmMonitoringSet);
        }
        $manager->flush();

        $infrastructureFolderPath = "./app/Resources/templates/v7.6.1/metricbeat";
        $finder = new Finder();
        $finder->files()->in($infrastructureFolderPath);
        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            if ($file->getRealPath() === false) {
                throw new \Exception("Error fetching file");
            }
            $template = new Template();
            $template->setName(strtolower($infrastructureMonitoringSet->getName() . "-" . str_replace(".json", "", $file->getFilename())));
            $template->setType(str_replace(".json", "", $file->getFilename()));
            $template->setContent((string) file_get_contents($file->getRealPath()));
            $template->setMonitoringSet($infrastructureMonitoringSet);
            $manager->persist($template);
            $manager->persist($infrastructureMonitoringSet);
        }
        $manager->flush();

	$logFolderPath = "./app/Resources/templates/v7.6.1/filebeat";
        $finder = new Finder();
        $finder->files()->in($logFolderPath);
        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            if ($file->getRealPath() === false) {
                throw new \Exception("Error fetching file");
            }
            $template = new Template();
            $template->setName(strtolower($logMonitoringSet->getName() . "-" . str_replace(".json", "", $file->getFilename())));
            $template->setType(str_replace(".json", "", $file->getFilename()));
            $template->setContent((string) file_get_contents($file->getRealPath()));
            $template->setMonitoringSet($logMonitoringSet);
            $manager->persist($template);
            $manager->persist($logMonitoringSet);
        }
        $manager->flush();

        $networkFolderPath = "./app/Resources/templates/v7.6.1/packetbeat";
        $finder = new Finder();
        $finder->files()->in($networkFolderPath);
        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            if ($file->getRealPath() === false) {
                throw new \Exception("Error fetching file");
            }
            $template = new Template();
            $template->setName(strtolower($networkMonitoringSet->getName() . "-" . str_replace(".json", "", $file->getFilename())));
            $template->setType(str_replace(".json", "", $file->getFilename()));
            $template->setContent((string) file_get_contents($file->getRealPath()));
            $template->setMonitoringSet($networkMonitoringSet);
            $manager->persist($template);
            $manager->persist($networkMonitoringSet);
        }
        $manager->flush();

    $uptimeFolderPath = "./app/Resources/templates/v7.6.1/heartbeat";
        $finder = new Finder();
        $finder->files()->in($uptimeFolderPath);
        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            if ($file->getRealPath() === false) {
                throw new \Exception("Error fetching file");
            }
            $template = new Template();
            $template->setName(strtolower($uptimeMonitoringSet->getName() . "-" . str_replace(".json", "", $file->getFilename())));
            $template->setType(str_replace(".json", "", $file->getFilename()));
            $template->setContent((string) file_get_contents($file->getRealPath()));
            $template->setMonitoringSet($uptimeMonitoringSet);
            $manager->persist($template);
            $manager->persist($uptimeMonitoringSet);
        }
        $manager->flush();





    }

    public function getOrder()
    {
        return 30;
    }
}
