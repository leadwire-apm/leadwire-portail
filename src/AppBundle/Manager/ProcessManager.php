<?php declare (strict_types = 1);

namespace AppBundle\Manager;

use AppBundle\Document\Process;
use AppBundle\Document\User;
use ATS\CoreBundle\Manager\AbstractManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

/**
 * Manager class for Process entities
 *
 * @see \ATS\CoreBundle\Manager\AbstractManager
 */
class ProcessManager extends AbstractManager
{
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, Process::class, $managerName);
    }

    /**
     *
     * @param User $user
     * @param string $message
     *
     * @return Process
     */
    public function create($user, $message): Process
    {
        $process = new Process();
        $process->setUser($user)->setMessage($message);

        $this->update($process);

        return $process;
    }

    /**
     * Create Login
     *
     * @param string $message
     *
     * @return Process
     */
    public function createLogin($message): Process
    {
        $process = new Process();
        $process->setIsNewLogin(true)->setMessage($message);

        $this->update($process);

        return $process;
    }
}
