<?php

namespace AppBundle\DataFixtures\MongoDB;

use AppBundle\Document\Template;
use Symfony\Component\Finder\Finder;
use AppBundle\Document\ApplicationType;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use AppBundle\DataFixtures\MongoDB\ApplicationTypeFixture;

class TemplateFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var ApplicationType $applicationType */
        $applicationType = $this->getReference(ApplicationTypeFixture::DEFAULT_TYPE_REFERENCE);
        $folderPath = "./app/Resources/templates";
        $finder = new Finder();
        $finder->files()->in($folderPath);
        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            if ($file->getRealPath() === false) {
                throw new \Exception("Error fetching file");
            }
            $template = new Template();
            $template->setName(str_replace(".json", "", $file->getFilename()));
            $template->setContent((string) file_get_contents($file->getRealPath()));
            $template->setApplicationType($applicationType);
            $template->setVersion("6.5.1");
            $manager->persist($template);
        }
        $manager->flush();
    }

    public function getOrder()
    {
        return 30;
    }
}
