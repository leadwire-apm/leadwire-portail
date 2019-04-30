<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\Application;
use JMS\Serializer\SerializerInterface;
use AppBundle\Manager\ApplicationManager;

class CuratorService
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ApplicationManager
     */
    private $applicationManager;

    /**
     * @var string
     */
    private $plateformName;

    public function __construct(
        SerializerInterface $serializer,
        ApplicationManager $applicationManager
    ) {
        $this->serializer = $serializer;
        $this->applicationManager = $applicationManager;
        $this->plateformName = 'tperf';
    }

    public function generateConfig()
    {
        $serialized = "";

        $applications = $this->applicationManager->getBy(['removed' => false]);
        /** @var Application $application */
        $entries = ['actions' => []];
        $index = 1;
        foreach ($applications as $application) {
            foreach ($application->getType()->getMonitoringSets() as $ms) {
                $entries['actions'][$index] =  [
                        'action' =>  'alias',
                        'description'=> "Add selected indices to or from the specified alias",
                        'options' => [
                            'name' => null,
                            'warn_if_no_indices' => true,
                            'continue_if_exception' => true,
                            'ignore_empty_list' => true,
                        ],
                        'add' => [
                            'filters' => [
                                'filtertype' => 'pattern',
                                'kind' => 'regex',
                                'value' => "^{$ms->getQualifier()}-.*-{$this->plateformName}-{$application->getName()}-.*$"

                            ]
                        ]
                ];
                $index++;
            }
        }
        $serialized .= $this->serializer->serialize($entries, 'yml');

        return $serialized;
    }
}
