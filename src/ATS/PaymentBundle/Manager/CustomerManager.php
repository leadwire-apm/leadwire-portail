<?php declare(strict_types=1);

namespace ATS\PaymentBundle\Manager;

use ATS\CoreBundle\Manager\AbstractManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use ATS\PaymentBundle\Document\Customer;

/**
 * Manager class for Customer entities
 *
 * @see \ATS\CoreBundle\Manager\AbstractManager
 */
class CustomerManager extends AbstractManager
{
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, Customer::class, $managerName);
    }
}
