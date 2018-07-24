<?php declare(strict_types=1);

namespace ATS\CoreBundle\Manager;

use ATS\CoreBundle\Manager\AbstractManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use ATS\CoreBundle\Document\Option;

/**
 * Manager class for Option entities
 *
 * @see \ATS\CoreBundle\Manager\AbstractManager
 */
class OptionManager extends AbstractManager
{
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, Option::class, $managerName);
    }
}
