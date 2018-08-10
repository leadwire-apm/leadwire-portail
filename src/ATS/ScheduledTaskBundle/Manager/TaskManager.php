<?php declare(strict_types=1);

namespace ATS\ScheduledTaskBundle\Manager;

use ATS\CoreBundle\Manager\AbstractManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use ATS\ScheduledTaskBundle\Document\Task;

/**
 * Manager class for Task entities
 *
 * @see \ATS\CoreBundle\Manager\AbstractManager
 */
class TaskManager extends AbstractManager
{
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, Task::class, $managerName);
    }
}
