<?php declare(strict_types=1);

namespace ATS\EmailBundle\Manager;

use ATS\EmailBundle\Document\Email;
use ATS\CoreBundle\Manager\AbstractManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

class EmailManager extends AbstractManager
{
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, Email::class, $managerName);
    }
}
