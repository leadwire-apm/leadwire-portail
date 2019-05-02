<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\MonitoringSet;
use AppBundle\Document\Template;
use AppBundle\Manager\MonitoringSetManager;
use AppBundle\Manager\TemplateManager;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\SerializerInterface;
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
     * @var MonitoringSetManager
     */
    private $msManager;

    public function __construct(
        TemplateManager $templateManager,
        MonitoringSetManager $msManager,
        SerializerInterface $serializer
    ) {
        $this->templateManager = $templateManager;
        $this->serializer = $serializer;
        $this->msManager = $msManager;
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
        $templates = $this->templateManager->getAll();

        /** @var Template $template */
        foreach ($templates as &$template) {
            $template->setAttachedMonitoringSets(new ArrayCollection($this->msManager->getAssosiated($template)));
        }
        return $templates;
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
}
