<?php declare (strict_types = 1);

namespace AppBundle\Manager;

use AppBundle\Document\AccessLevel;
use ATS\CoreBundle\Manager\AbstractManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

/**
 * Manager class for AccessLevel entities
 *
 * @see \ATS\CoreBundle\Manager\AbstractManager
 */
class AccessLevelManager extends AbstractManager
{
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, AccessLevel::class, $managerName);
    }

}
