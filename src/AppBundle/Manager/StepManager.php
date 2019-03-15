<?php declare (strict_types = 1);

namespace AppBundle\Manager;

use AppBundle\Document\Step;
use AppBundle\Document\Tmec;
use ATS\CoreBundle\Manager\AbstractManager;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

/**
 * Manager class for Step entities
 *
 * @see \ATS\CoreBundle\Manager\AbstractManager
 */
class StepManager extends AbstractManager
{
    public function __construct(ManagerRegistry $managerRegistry, $managerName = null)
    {
        parent::__construct($managerRegistry, Step::class, $managerName);
    }
    
    /**
     *
     * @param Tmec $tmec
     * @param string $label
     * @param int $order
     *
     * @return Step
     */
    public function create($tmec, $label, $order): Step
    {
        $step = new Step();
        $step
            ->setTmec($tmec)
            ->setLabel($label)
            ->setOrder($order)
            ->setComment("")
            ->setWaiting(false)
            ->setCurrent(false)
            ->setCompleted(false);

        $this->update($step);

        return $step;
    }
}
