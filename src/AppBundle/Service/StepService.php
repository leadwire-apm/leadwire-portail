<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\Step;
use Symfony\Component\Finder\Finder;
use AppBundle\Manager\StepManager;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

    public function __construct(
        StepManager $stepManager,
        SerializerInterface $serializer
    ) {
        $this->stepManager = $stepManager;
        $this->serializer = $serializer;
    }

    /**
     * @param string $compagne
     */
    public function initSteps(string $compagne)
    {
       // $step = $this->stepManager->getBy(['compagne'=> $compagne]);

        //if ($step === null) {
            $this->stepManager->create($compagne, "Cadrage", 1);
            $this->stepManager->create($compagne, "Devis", 2);
            $this->stepManager->create($compagne, "CDC", 3);
            $this->stepManager->create($compagne, "R7J", 4);
            $this->stepManager->create($compagne, "Scipts Jdd", 5);
            $this->stepManager->create($compagne, "PP", 6);
            $this->stepManager->create($compagne, "Outils Tperf", 7);
            $this->stepManager->create($compagne, "Tuning", 8);
            $this->stepManager->create($compagne, "Ref", 9);
            $this->stepManager->create($compagne, "Rapport", 10);
       // } else {
        //    throw new AccessDeniedHttpException("Compagne already have steps");
        //}
        return $step;
    }

    public function update($json)
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
        $step = $this->stepManager->getBy($compagne);
        return $step;
    }
}
