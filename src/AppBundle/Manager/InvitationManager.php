<?php declare(strict_types=1);

namespace AppBundle\Manager;

use ATS\CoreBundle\Manager\AbstractManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use AppBundle\Document\Invitation;

/**
 * Manager class for Invitation entities
 *
 * @see \ATS\CoreBundle\Manager\AbstractManager
 */
class InvitationManager extends AbstractManager
{
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, Invitation::class, $managerName);
    }
}
