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
     * @param boolean $current
     *
     * @return Step
     */
    public function create($tmec, $label, $order, $current): Step
    {
        $step = new Step();
        $step
            ->setTmec($tmec)
            ->setLabel($label)
            ->setOrder($order)
            ->setComment("")
            ->setWaiting(false)
            ->setCurrent($current)
            ->setCompleted(false)
            ->setDate("");

        $this->update($step);

        return $step;
    }
}
