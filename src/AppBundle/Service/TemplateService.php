<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\ApplicationType;
use AppBundle\Document\MonitoringSet;
use AppBundle\Document\Template;
use AppBundle\Manager\ApplicationTypeManager;
use AppBundle\Manager\MonitoringSetManager;
use AppBundle\Manager\TemplateManager;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

    /**
     * @var ApplicationTypeManager
     */
    private $applicationTypeManager;

    /**
     * @var MonitoringSetManager
     */
    private $msManager;

    /**
     * @var string
     */
    private $defaultTemplatesPath;

    public function __construct(
        TemplateManager $templateManager,
        ApplicationTypeManager $applicationTypeManager,
        MonitoringSetManager $msManager,
        SerializerInterface $serializer,
        string $defaultTemplatesPath
    ) {
        $this->templateManager = $templateManager;
        $this->serializer = $serializer;
        $this->applicationTypeManager = $applicationTypeManager;
        $this->msManager = $msManager;
        $this->defaultTemplatesPath = $defaultTemplatesPath;
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
     * @return \MongoId
     */
    public function deleteTemplate(string $id)
    {
        $template = $this->templateManager->getOneBy(['id' => $id]);
        if ($template === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "Template not Found");
        } else {
            return $this->templateManager->delete($template);
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

    /**
     * @param string $id
     */
    public function initializeDefaultForApplicationType(string $id)
    {
        // TODO review this !!

        foreach ($this->msManager->getAll() as $ms) {
            $finder = new Finder();
            $finder->files()->in($this->defaultTemplatesPath . \strtolower($ms->getQualifier()));
            foreach ($finder as $file) {
                if ($file->getRealPath() === false) {
                    throw new \Exception("Error fetching file");
                }
                $template = new Template();
                $template->setName(\strtolower($ms->getName() . "-" . \str_replace(".json", "", $file->getFilename())));
                $template->setType(\str_replace(".json", "", $file->getFilename()));
                $template->setContent((string) file_get_contents($file->getRealPath()));
                $template->setMonitoringSet($ms);

                $this->templateManager->update($template);
            }
        }
    }
}
