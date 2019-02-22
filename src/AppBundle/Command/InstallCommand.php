<?php

namespace AppBundle\Command;

use AppBundle\Service\LdapService;
use AppBundle\Service\TemplateService;
use AppBundle\Service\ApplicationService;
use Doctrine\ODM\MongoDB\DocumentManager;
use ATS\PaymentBundle\Service\PlanService;
use AppBundle\Service\ApplicationTypeService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class InstallCommand extends ContainerAwareCommand
{
    /**
     * @var ApplicationService
     */
    private $applicationService;

    /**
     * @var LdapService
     */
    private $ldapService;

    /**
     * @var ApplicationTypeService
     */
    private $applicationTypeService;

    /**
     * @var PlanService
     */
    private $planService;

    /**
     * @var TemplateService
     */
    private $templateService;

    /**
     * @var DocumentManager
     */
    private $dm;

    public function __construct(
        ApplicationService $applicationService,
        ApplicationTypeService $applicationTypeService,
        LdapService $ldapService,
        PlanService $planService,
        TemplateService $templateService,
        DocumentManager $doctrine
    ) {
        $this->applicationService = $applicationService;
        $this->applicationTypeService = $applicationTypeService;
        $this->ldapService = $ldapService;
        $this->planService = $planService;
        $this->templateService = $templateService;
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
        /** @var bool $debug */
        $debug = $this->getContainer()->getParameter('kernel.debug');
        /**
         * Assets install and dump.
         */
        $commands = [
            "Install Assets" => [
                'command' => "assets:install",
                '--symlink' => $debug,
                '--env' => $debug === true ? 'dev' : 'prod',
            ],
            "Assetic Dump" => [
                'command' => "assetic:dump",
                '--env' => $debug === true ? 'dev' : 'prod',
            ],
        ];

        $output->writeln("Installing Assets");
        foreach ($commands as $step => $arguments) {
            $output->writeln($step);
            $command = $this->getApplication()->find($arguments['command']);
            $input = new ArrayInput($arguments);
            $command->run($input, $output);
        }

        try {
            $output->writeln("Create Default Application Type if not set");
            $defaultType = $this->applicationTypeService->createDefaultType();
            $output->writeln("<fg=green>OK</>");

            $output->writeln("Create Demo applications");
            $this->applicationService->createDemoApplications();
            $this->ldapService->createDemoApplicationsEntries();
            // $this->esService->createDempAppliactionsIndexPatterns();
            $output->writeln("<fg=green>OK</>");

            $output->writeln("Create Plans if not set");
            $this->planService->createDefaulPlans();
            $output->writeln("<fg=green> 3 Plans are created !</>");

            $output->writeln("Create default templates");
            $this->templateService->createDefaultTemplates($this->getContainer()->getParameter('kernel.project_dir').'/app/Resources/templates', $defaultType);
            $output->writeln("<fg=green> OK</>");

            $output->writeln("Creating MongoDB Indexes");
            $this->dm->getSchemaManager()->ensureIndexes();
            $output->writeln("");
            $output->writeln("");

            $output->writeln("<fg=green>All OK</>");
        } catch (\Exception $e) {
            $output->writeln("<fg=red>" . $e->getMessage() . "</>");
        }
    }
}
