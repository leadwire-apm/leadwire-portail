<?php declare (strict_types = 1);

namespace AppBundle\Manager;

use AppBundle\Document\Watcher;
use ATS\CoreBundle\Manager\AbstractManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

/**
 * Manager class for Watcher entities
 *
 * @see \ATS\CoreBundle\Manager\AbstractManager
 */
class WatcherManager extends AbstractManager
{
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, Watcher::class, $managerName);
    }

}
