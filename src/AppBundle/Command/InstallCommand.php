<?php

namespace AppBundle\Command;

use AppBundle\Service\LdapService;
use AppBundle\Document\Application;
use AppBundle\Service\KibanaService;
use AppBundle\Service\EnvironmentService;
use AppBundle\Service\ApplicationService;
use AppBundle\Service\SearchGuardService;
use Doctrine\ODM\MongoDB\DocumentManager;
use ATS\PaymentBundle\Service\PlanService;
use AppBundle\Service\ElasticSearchService;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Doctrine\Common\DataFixtures\Executor\MongoDBExecutor;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use AppBundle\Service\CuratorService;

class InstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('leadwire:install')
            ->setDescription('Creates files and data required by the app')
            ->addOption("purge", "p", InputOption::VALUE_NONE, "Purge the database")
            ->setHelp(
                'Creates files and data required by the app.
Load default Application Type. Insert template for Kibana and more..'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var LdapService $ldap */
        //$ldap = $this->getContainer()->get(LdapService::class);
        /** @var ElasticSearchService $es */
        $es = $this->getContainer()->get(ElasticSearchService::class);
        /** @var KibanaService $kibana */
        $kibana = $this->getContainer()->get(KibanaService::class);
        /** @var PlanService $planService */
        $planService = $this->getContainer()->get(PlanService::class);
        /** @var EnvironmentService $curatorService */
        $environmentService = $this->getContainer()->get(EnvironmentService::class);
        /** @var ApplicationService $applicationService */
        $applicationService = $this->getContainer()->get(ApplicationService::class);
        /** @var SearchGuardService $sgService */
        //$sgService = $this->getContainer()->get(SearchGuardService::class);
        /** @var CuratorService $curatorService */
        $curatorService = $this->getContainer()->get(CuratorService::class);

        /** @var bool $stripeEnabled */
        $stripeEnabled = $this->getContainer()->getParameter('stripe_enabled');

        /** @var bool $purge */
        $purge = $input->getOption("purge") === true ?: false;

        if ($stripeEnabled === true) {
            $this->display($output, "Deleting Stripe plans");
            $planService->deleteAllPlans();
        }

        $this->loadFixtures($output, $purge);
        
        //Purge Elasticsearch
        $this->purgeES($output, $purge, $es);

        //$this->display($output, "Creating LDAP entries for demo applications");
        //$ldap->createDemoApplicationsEntries();
        $demoApplications = $applicationService->listDemoApplications();

        //$this->display($output, "Initializing SearchGuard configuration");
        //$sgService->updateSearchGuardConfig();

        $this->display($output, "Initializing ES & Kibana");
        /** @var Application $application */
        foreach ($demoApplications as $application) {
            //$es->deleteIndex($appIndex);
            $sharedIndex = "staging-" . $application->getSharedIndex();
            $appIndex = "staging-" . $application->getApplicationIndex();
            $patternIndex = "*-staging-" . $application->getName() . "-*";
           
            $es->createTenant($appIndex);
            $es->createTenant($sharedIndex);

            $es->createIndexTemplate($application, $applicationService->getActiveApplicationsNames());
           //$es->createAlias($application, "staging");
            $kibana->loadIndexPatternForApplication(
                $application,
                $appIndex,
                "staging"
            );

            $kibana->createApplicationDashboards($application, "staging");

            //$es->deleteIndex($sharedIndex);

            $kibana->loadIndexPatternForApplication(
                $application,
                $sharedIndex,
                "staging"
            );

            $kibana->loadDefaultIndex($appIndex, 'default');
            $kibana->makeDefaultIndex($appIndex, 'default');

            $kibana->loadDefaultIndex($sharedIndex, 'default');
            $kibana->makeDefaultIndex($sharedIndex, 'default');

   
            $es->createRole("staging", $application->getName(), array($patternIndex), array($sharedIndex, $appIndex), array("read"));
            $es->createRoleMapping("staging", $application->getName());
        }

        if ($stripeEnabled === true) {
            $this->display($output, "Creating Stripe Plans with new Data");
            $planService->createDefaultPlans();
        }

        //$curatorService->updateCuratorConfig();

        exec('npm stop');
        exec('npm install');
        exec('npm start');

        return 0;
    }

    
    
     private function purgeES($output, $purge, $es)
    {
        if ($purge === false) {
            return;
        }
         
       $es->purgeIndices();
       $es->purgeAliases();
       $es->purgeTemplates();
        
    }
         
         
    private function loadFixtures($output, $purge)
    {
        if ($purge === false) {
            return;
        }

        /** @var DocumentManager $dm */
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();
        /** @var KernelInterface $kernel */
        $kernel = $this->getContainer()->get('kernel');
        $paths = $this->getContainer()->getParameter('doctrine_mongodb.odm.fixtures_dirs');
        $paths = is_array($paths) === true ? $paths : [$paths];
        $paths[] = $kernel->getRootDir() . '/DataFixtures/MongoDB';
        foreach ($kernel->getBundles() as $bundle) {
            $paths[] = $bundle->getPath() . '/DataFixtures/MongoDB';
        }

        $loaderClass = $this->getContainer()->getParameter('doctrine_mongodb.odm.fixture_loader');
        $loader = new $loaderClass($this->getContainer());
        foreach ($paths as $path) {
            if (is_dir($path) === true) {
                $loader->loadFromDirectory($path);
            } else if (is_file($path) === true) {
                $loader->loadFromFile($path);
            }
        }

        $fixtures = $loader->getFixtures();
        if ($fixtures === null) {
            throw new \InvalidArgumentException(
                sprintf('Could not find any fixtures to load in: %s', "\n\n- " . implode("\n- ", $paths))
            );
        }

        $purger = new MongoDBPurger($dm);
        $executor = new MongoDBExecutor($dm, $purger);
        $executor->setLogger(
            function ($message) use ($output) {
                $output->writeln(sprintf('  <comment>></comment> <info>%s</info>', $message));
            }
        );
        $executor->execute($fixtures, false);
    }

    private function display($output, $message)
    {
        $output->writeln(sprintf('  <comment>></comment> <info>%s</info>', $message));
    }
}
