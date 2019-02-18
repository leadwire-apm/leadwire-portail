<?php declare (strict_types = 1);

namespace AppBundle\Manager;

use AppBundle\Document\Template;
use ATS\CoreBundle\Manager\AbstractManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

class TemplateManager extends AbstractManager
{
    /**
     * @codeCoverageIgnore
     *
     * @param ManagerRegistry $managerRegistry
     * @param ?string $managerName
     */
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, Template::class, $managerName);
    }
}
