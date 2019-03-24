<?php declare (strict_types = 1);

namespace AppBundle\Manager;

use AppBundle\Document\ActivationCode;
use ATS\CoreBundle\Manager\AbstractManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

class ActivationCodeManager extends AbstractManager
{
    /**
     * @codeCoverageIgnore
     *
     * @param ManagerRegistry $managerRegistry
     * @param ?string $managerName
     */
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, ActivationCode::class, $managerName);
    }
}
