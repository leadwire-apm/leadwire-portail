<?php

namespace AppBundle\Command;

use AppBundle\Service\ApplicationTypeService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    private $applicationTypeService;

    public function __construct(ApplicationTypeService $applicationTypeService)
    {
        $this->applicationTypeService = $applicationTypeService;

        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setName('leadwire:install')
            ->setDescription('Creates files and data required by the app.')
            ->setHelp('Creates files and data required by the app.
            load default Application Type. Insert template for Kibana and more..')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Create Application Type");

        try {
            $defaultType = $this->applicationTypeService->createDefaultType();
        } catch (\Exception $e) {
            $output->writeln("<fg=red>" . $e->getMessage() . "</>");
            return;
        }

        $output->writeln("<fg=green>Application Type: " . $defaultType->getName() . "</>");
        $output->writeln("<fg=green>It's done!</>");
    }
}
