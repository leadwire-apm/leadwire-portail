<?php declare (strict_types = 1);

namespace AppBundle\Manager;

use AppBundle\Document\Step;
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
     * @param string $compagne
     * @param string $label
     * @param int $order
     *
     * @return Step
     */
    public function create($compagne, $label, $order): Step
    {
        $step = new Step();
        $step
            ->setCompagne($compagne)
            ->setLabel($label)
            ->setOrder($order)
            ->setComment("")
            ->setWaiting(false);

        $this->update($step);

        return $step;
    }
}
