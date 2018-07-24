<?php declare(strict_types=1);

namespace ATS\AnalyticsBundle\Manager;

use ATS\CoreBundle\Manager\AbstractManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use ATS\AnalyticsBundle\Document\LoginStatistic;

/**
 * Manager class for LoginStatistic entities
 *
 * @see \ATS\CoreBundle\Manager\AbstractManager
 */
class LoginStatisticManager extends AbstractManager
{
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, LoginStatistic::class, $managerName);
    }
}
