<?php declare (strict_types = 1);

namespace AppBundle\Service;

use AppBundle\Document\Application;
use AppBundle\Manager\ApplicationManager;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Psr\Log\LoggerInterface;

class CuratorService
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ApplicationManager
     */
    private $applicationManager;

    /**
     * @var array
     */
    private $config;

    public function __construct(
        SerializerInterface $serializer,
        ApplicationManager $applicationManager,
        LoggerInterface $logger,
        array $curatorConfig
    ) {
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->applicationManager = $applicationManager;
        $this->config = $curatorConfig;
    }

    public function generateConfig()
    {
        $platformName = $this->config['platformName'];
        $applications = $this->applicationManager->getBy(['removed' => false]);
        /** @var Application $application */
        $entries = ['actions' => []];
        $index = 1;
        foreach ($applications as $application) {
            foreach ($application->getType()->getMonitoringSets() as $ms) {
                $qualifier = strtolower($ms->getQualifier());
                $entries['actions'][$index] = [
                    'action' => 'alias',
                    'description' => "Add selected indices to or from the specified alias",
                    'options' => [
                        'name' => "{$qualifier}-test-{$application->getName()}",
                        'warn_if_no_indices' => true,
                        'continue_if_exception' => true,
                        'ignore_empty_list' => true,
                    ],
                    'add' => [
                        'filters' => [
                            [
                                'filtertype' => 'pattern',
                                'kind' => 'regex',
                                'value' => "^{$qualifier}-.*-{$platformName}-test-{$application->getName()}-.*$",
                            ],
                        ],
                    ],
                ];
                $index++;
            }
        }
        $serialized = $this->serializer->serialize($entries, 'yml');

        return $serialized;
    }

    /**
     *  * /usr/bin/curator --config /path/to/curator-config.yml /path/to/curator-alias.yml
     *
     * @return void
     */
    public function updateCuratorConfig()
    {
        $fs = new Filesystem();
        $configFile = $this->config['config_path'];
        $scriptPath = $this->config['script_path'];

        $curatorConfig = $this->generateConfig();

        // Delete previous files if any
        if (\is_file('curator-alias.yml') === true) {
            \unlink('curator-alias.yml');
        }

        try {
            $fs->dumpFile('curator-alias.yml', $curatorConfig);

            // ! Hard coded on purpose
            $output = \shell_exec("$scriptPath --config $configFile curator-alias.yml &");
            $this->logger->notice(
                "leadwire.curator.updateCuratorConfig",
                [
                    'curator_output' => $output,
                ]
            );
        } catch (IOException $e) {
            $this->logger->critical("leadwire.curator.updateCuratorConfig", ['error' => $e->getMessage()]);
        }
    }
}
