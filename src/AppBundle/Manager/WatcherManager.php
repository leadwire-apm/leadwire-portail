<?php declare (strict_types = 1);

namespace AppBundle\Manager;

use AppBundle\Document\Watcher;
use ATS\CoreBundle\Manager\AbstractManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

/**
 * Manager class for Watcher entities
 *
 * @see \ATS\CoreBundle\Manager\AbstractManager
 */
class WatcherManager extends AbstractManager
{
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, Watcher::class, $managerName);
    }

     /**
     *
     * @param string $dashboard
     * @param string $envId
     *
     * @return Watcher
     */
    public function getByEnvDash($dashboard, $envId)
    {
        /** @var Watcher $watcherList */
        $watcherList =  $this->getDocumentRepository()->
            createQueryBuilder()
            ->find()
            ->field('dashboard')->equals($dashboard)
            ->field('envId')->equals($envId)
            ->getQuery()
            ->execute()
            ->toArray(false);
        return $watcherList;
    }

}
