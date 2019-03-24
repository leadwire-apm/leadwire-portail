<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\Template;
use Symfony\Component\Finder\Finder;
use AppBundle\Manager\TemplateManager;
use AppBundle\Document\ApplicationType;
use JMS\Serializer\SerializerInterface;
use AppBundle\Manager\ApplicationTypeManager;
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

    public function __construct(
        TemplateManager $templateManager,
        ApplicationTypeManager $applicationTypeManager,
        SerializerInterface $serializer
    ) {
        $this->templateManager = $templateManager;
        $this->serializer = $serializer;
        $this->applicationTypeManager = $applicationTypeManager;
    }

    public function newTemplate($json)
    {
        /** @var Template $template */
        $template = $this
            ->serializer
            ->deserialize($json, Template::class, 'json');

        /**
         * * Author's note :
         * * For some reason, Doctrine complains about cascade operations when flushing the unit of work on PHP 7.1.x (works on 7.2+)
         * * Workaround -> manually fetch the applicationType from the ID
         */

        $applicationType = $this->applicationTypeManager->getOneBy(['id' => $template->getApplicationType()->getId()]);

        $oldTemplate = $this->templateManager->getOneBy(['applicationType.id' => $template->getApplicationType()->getId(), 'name' => $template->getName()]);

        if ($oldTemplate instanceof Template) {
            $version = $oldTemplate->getVersion() + 1;
        } else {
            $version = 1;
        }

        $template->setVersion($version);
        $template->setApplicationType($applicationType);

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
}
