<?php declare (strict_types = 1);

namespace AppBundle\Manager;

use AppBundle\Document\MonitoringSet;
use ATS\CoreBundle\Manager\AbstractManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

class MonitoringSetManager extends AbstractManager
{
    /**
     * @codeCoverageIgnore
     *
     * @param ManagerRegistry $managerRegistry
     * @param ?string $managerName
     */
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, MonitoringSet::class, $managerName);
    }

    public function getValid()
    {
        return $this
            ->qb()
            ->field('templates')->size(MonitoringSet::TEMPLATES_COUNT)
            ->getQuery()
            ->execute()
            ->toArray(false);
    }
}
