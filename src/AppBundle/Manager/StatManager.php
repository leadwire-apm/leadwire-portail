<?php declare(strict_types=1);

namespace AppBundle\Manager;

use ATS\CoreBundle\Manager\AbstractManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use AppBundle\Document\Stat;

/**
 * Manager class for Stat entities
 *
 * @see \ATS\CoreBundle\Manager\AbstractManager
 */
class StatManager extends AbstractManager
{
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, Stat::class, $managerName);
    }
}
