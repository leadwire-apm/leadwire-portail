<?php declare (strict_types = 1);

namespace AppBundle\Manager;

use AppBundle\Document\Environment;
use ATS\CoreBundle\Manager\AbstractManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

/**
 * Manager class for Environment entities
 *
 * @see \ATS\CoreBundle\Manager\AbstractManager
 */
class EnvironmentManager extends AbstractManager
{
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, Environment::class, $managerName);
    }

}
