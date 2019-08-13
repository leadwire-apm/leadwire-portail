<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\Step;
use AppBundle\Document\Tmec;
use AppBundle\Manager\StepManager;
use JMS\Serializer\SerializerInterface;

class StepService
{
    /**
     * @var StepManager
     */
    private $stepManager;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(StepManager $stepManager, SerializerInterface $serializer)
    {
        $this->stepManager = $stepManager;
        $this->serializer = $serializer;
    }

    /**
     * @param Tmec $tmec
     */
    public function initSteps(Tmec $tmec)
    {
        $this->stepManager->create($tmec, "Cadrage", 1, true);
        $this->stepManager->create($tmec, "Cahier de charge", 2, false);
        $this->stepManager->create($tmec, "Recette jmeter", 3, false);
        $this->stepManager->create($tmec, "Scripts", 4, false);
        $this->stepManager->create($tmec, "DUMP", 5, false);
        $this->stepManager->create($tmec, "Jeux de données", 6, false);
        $this->stepManager->create($tmec, "TMEC", 7, false);
        $this->stepManager->create($tmec, "Rapport", 8, false);

        return null;
    }

    public function updateStep($json)
    {
        /** @var Step $step */
        $step = $this->serializer->deserialize($json, Step::class, 'json');
        $this->stepManager->update($step);
    }

    /**
     * @param array $params
     */
    public function list(array $params)
    {
        $step = $this->stepManager->getBy($params);

        return $step;
    }
}
