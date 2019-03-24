<?php declare (strict_types = 1);

namespace AppBundle\Manager;

use AppBundle\Document\DeleteTask;
use ATS\CoreBundle\Manager\AbstractManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

/**
 * Manager class for App entities
 *
 * @see \ATS\CoreBundle\Manager\AbstractManager
 */
class DeleteTaskManager extends AbstractManager
{
    /**
     * @codeCoverageIgnore
     *
     * @param ManagerRegistry $managerRegistry
     * @param ?string $managerName
     */
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, DeleteTask::class, $managerName);
    }
}
