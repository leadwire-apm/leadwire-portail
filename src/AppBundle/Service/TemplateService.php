<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\Template;
use AppBundle\Manager\TemplateManager;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Finder\Finder;

class TemplateService
{
    /**
     * @var TemplateManager
     */
    private $templateManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        TemplateManager $templateManager,
        SerializerInterface $serializer
    ) {
        $this->templateManager = $templateManager;
        $this->serializer = $serializer;
    }

    public function newTemplate($json)
    {
        /** @var Template $template */
        $template = $this
            ->serializer
            ->deserialize($json, Template::class, 'json');

        $id = $this->templateManager->update($template);

        return $id;
    }

    public function updateTemplate($json)
    {
        /** @var Template $template */
        $template = $this->serializer->deserialize($json, Template::class, 'json');

        $this->templateManager->update($template);
    }

    /**
     * @return array
     */
    public function listTemplates(): array
    {
        return $this->templateManager->getAll();
    }

    /**
     * @param string $id
     *
     * @return void
     */
    public function deleteTemplate(string $id)
    {
        $this->templateManager->delete($id);
    }

    public function createDefaultTemplates(string $folderPath)
    {
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
            $template->setVersion(1);
            $this->templateManager->update($template);
        }
    }


    /**
     * @param string $id
     *
     * @return Template|null
     */
    public function getTemplate(string $id)
    {
        return $this->templateManager->getOneBy(['id' => $id]);
    }
}
