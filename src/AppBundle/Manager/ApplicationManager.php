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
    /**
     * @codeCoverageIgnore
     *
     * @param ManagerRegistry $managerRegistry
     * @param ?string $managerName
     */
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, Application::class, $managerName);
    }

    public function getActiveApplicationsNames()
    {
        return $this
            ->qb()
            ->select('name')
            ->field('removed')->equals(false)
            ->getQuery()
            ->execute()
            ->toArray(false);
    }

    public function getDemoApplications()
    {
        return $this
            ->getDocumentRepository()
            ->findBy(['demo' => true]);
    }
}
