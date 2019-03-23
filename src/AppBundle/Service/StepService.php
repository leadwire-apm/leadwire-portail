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
            ),
            array(
                "label"=> "Devis",
                "order"=> 2,
                "current"=> false
            ),
            array(
                "label"=> "CDC",
                "order"=> 3,
                "current"=> false
            ),
            array(
                "label"=> "R7J",
                "order"=> 4,
                "current"=> false
            ),
            array(
                "label"=> "Scipts Jdd",
                "order"=> 5,
                "current"=> false
            ),
            array(
                "label"=> "PP",
                "order"=> 6,
                "current"=> false
            ),
            array(
                "label"=> "Outils Tperf",
                "order"=> 7,
                "current"=> false
            ),
            array(
                "label"=> "Tuning",
                "order"=> 8,
                "current"=> false
            ),
            array(
                "label"=> "Ref",
                "order"=> 9,
                "current"=> false
            ),
            array(
                "label"=> "Rapport",
                "order"=> 10,
                "current"=> false
            )
        );

        foreach($stepsList as $step)
        {
            $this->stepManager->create($tmec, $step["label"], $step["order"], $step["current"]);
        }
       
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
