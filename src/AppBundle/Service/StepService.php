<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\Step;
use AppBundle\Document\Tmec;
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
     * @param Tmec $tmec
     */
    public function initSteps(Tmec $tmec)
    {
        $stepsList = array(
            array(
                "label"=> "Cadrage",
                "order"=> 1,
                "current"=> true
            )
        );

        foreach($stepsList as $step)
        {
            $this->stepManager->create($tmec, $step["label"], $step["order"], $step["current"]);
        }
       /* $this->stepManager->create($tmec, "Cadrage", 1, true);
        $this->stepManager->create($tmec, "Devis", 2, false);
        $this->stepManager->create($tmec, "CDC", 3, false);
        $this->stepManager->create($tmec, "R7J", 4, false);
        $this->stepManager->create($tmec, "Scipts Jdd", 5, false);
        $this->stepManager->create($tmec, "PP", 6, false);
        $this->stepManager->create($tmec, "Outils Tperf", 7, false);
        $this->stepManager->create($tmec, "Tuning", 8, false);
        $this->stepManager->create($tmec, "Ref", 9, false);
        $this->stepManager->create($tmec, "Rapport", 10, false);*/
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
