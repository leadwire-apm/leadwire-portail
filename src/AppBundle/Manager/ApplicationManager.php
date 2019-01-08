<?php declare(strict_types=1);

namespace AppBundle\Manager;

use ATS\CoreBundle\Manager\AbstractManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use AppBundle\Document\Application;

/**
 * Manager class for App entities
 *
 * @see \ATS\CoreBundle\Manager\AbstractManager
 */
class ApplicationManager extends AbstractManager
{
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, Application::class, $managerName);
    }
}
