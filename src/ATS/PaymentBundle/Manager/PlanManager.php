<?php declare(strict_types=1);

namespace ATS\PaymentBundle\Manager;

use ATS\CoreBundle\Manager\AbstractManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use ATS\PaymentBundle\Document\Plan;

/**
 * Manager class for Plan entities
 *
 * @see \ATS\CoreBundle\Manager\AbstractManager
 */
class PlanManager extends AbstractManager
{
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, Plan::class, $managerName);
    }
}
