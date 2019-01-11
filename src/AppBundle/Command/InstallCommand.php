<?php

namespace AppBundle\Command;

use AppBundle\Service\ApplicationTypeService;
use ATS\PaymentBundle\Service\PlanService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    /**
     * @var ApplicationTypeService
     */
    private $applicationTypeService;
    /**
     * @var PlanService
     */
    private $planService;

    /**
     * @var DocumentManager
     */
    private $dm;

    public function __construct(
        ApplicationTypeService $applicationTypeService,
        PlanService $planService,
        DocumentManager $doctrine
    ) {
        $this->applicationTypeService = $applicationTypeService;
        $this->planService = $planService;
        $this->dm = $doctrine;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('leadwire:install')
            ->setDescription('Creates files and data required by the app.')
            ->setHelp(
                'Creates files and data required by the app.
Load default Application Type. Insert template for Kibana and more..'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * Assets install and dump.
         */
        $commands = [
            "Install Assets" => [
                'command' => "assets:install",
                '--symlink' => true,
            ],
            "Assetic Dump" => [
                'command' => "assetic:dump",
            ],
        ];

        foreach ($commands as $step => $arguments) {
            $output->writeln($step);
            $command = $this->getApplication()->find($arguments['command']);
            $greetInput = new ArrayInput($arguments);
            $command->run($greetInput, $output);
        }

        $output->writeln("Create Application Type if not set yet");

        try {
            $defaultType = $this->applicationTypeService->createDefaultType();
        } catch (\Exception $e) {
            $output->writeln("<fg=red>" . $e->getMessage() . "</>");
            return;
        }
        $output->writeln("<fg=green>Application Type: " . $defaultType->getName() . "</>");

        $output->writeln("Create Plans if not set");

        try {
            $this->planService->createDefaulPlans();
            $output->writeln("<fg=green> 3 Plans  are created !</>");
        } catch (\Exception $e) {
            $output->writeln("<fg=red>" . $e->getMessage() . "</>");
            return;
        }

        $output->writeln("Creating Indexes");
        $this->dm->getSchemaManager()->ensureIndexes();

        $output->writeln("<fg=green>It's done!</>");
    }
}
