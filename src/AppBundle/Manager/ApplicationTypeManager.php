<?php declare(strict_types=1);

namespace AppBundle\Manager;

use ATS\CoreBundle\Manager\AbstractManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use AppBundle\Document\ApplicationType;
use AppBundle\Document\MonitoringSet;

/**
 * Manager class for ApplicationType entities
 *
 * @see \ATS\CoreBundle\Manager\AbstractManager
 */
class ApplicationTypeManager extends AbstractManager
{
    /**
     * @codeCoverageIgnore
     *
     * @param ManagerRegistry $managerRegistry
     * @param ?string $managerName
     */
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, ApplicationType::class, $managerName);
    }

    public function getLinkedTypes(MonitoringSet $ms)
    {
        return $this
            ->qb()
            ->field('monitoringSets.id')->equals((string) $ms->getId())
            ->getQuery()
            ->execute()
            ->toArray(false);
    }
}
