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
        $apmFolderPath = "./app/Resources/templates/apm";
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
        $infrastructureFolderPath = "./app/Resources/templates/metricbeat";
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
    }

    public function getOrder()
    {
        return 30;
    }
}
