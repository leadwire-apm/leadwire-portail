<?php

namespace AppBundle\Command;

use AppBundle\Service\ApplicationTypeService;
use ATS\PaymentBundle\Service\PlanService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

class InstallCommand extends Command
{
    private $applicationTypeService;
    private $planService;

    public function __construct(ApplicationTypeService $applicationTypeService, PlanService $planService)
    {
        $this->applicationTypeService = $applicationTypeService;
        $this->planService = $planService;

        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setName('leadwire:install')
            ->setDescription('Creates files and data required by the app.')
            ->addArgument('dev', InputArgument::OPTIONAL, "If set, ignore grunt build step")
            ->setHelp('Creates files and data required by the app.
Load default Application Type. Insert template for Kibana and more..')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isDev = $input->getArgument('dev');

        if (!$isDev) {
            /**
             * Create Build of assets first.
             */
            $output->writeln("<fg=yellow>Dev mode off, executing grunt build...</>");

            $outputGrunt = shell_exec("cd src/UIBundle/Resources/public/dev && grunt build");
            $output->writeln($outputGrunt);
            shell_exec(" cd ../../../../../");
        } else {
            $output->writeln("<fg=yellow>Dev mode on going to grunt build...</>");
        }

        /**
         * Assets install and dump.
         */
        $commands = [
            "Install Assets" => [
                'command' => "assets:install",
                '--symlink' => true,
            ] ,
            "Assetic Dump" => [
                'command' => "assetic:dump",
            ]
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

        $output->writeln("<fg=green>It's done!</>");
    }
}
