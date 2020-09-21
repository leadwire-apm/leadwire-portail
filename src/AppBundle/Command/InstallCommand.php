<?php

namespace AppBundle\Command;

use AppBundle\Service\LdapService;
use AppBundle\Document\Application;
use AppBundle\Service\KibanaService;
use AppBundle\Service\EnvironmentService;
use AppBundle\Service\ApplicationService;
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
use AppBundle\Service\UserService;
use AppBundle\Manager\ApplicationManager;


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

    protected function execute(InputInterface $input, OutputInterface $output) {
        /** @var LdapService $ldap */
        $ldap = $this->getContainer()->get(LdapService::class);
        /** @var ElasticSearchService $es */
        $es = $this->getContainer()->get(ElasticSearchService::class);
        /** @var KibanaService $kibana */
        $kibana = $this->getContainer()->get(KibanaService::class);
        /** @var PlanService $planService */
        $planService = $this->getContainer()->get(PlanService::class);
        /** @var EnvironmentService $environmentService */
        $environmentService = $this->getContainer()->get(EnvironmentService::class);
        /** @var ApplicationService $applicationService */
        $applicationService = $this->getContainer()->get(ApplicationService::class);
        /** @var UserService $userService */
        $userService = $this->getContainer()->get(UserService::class);
        
        /**
         * @var ApplicationManager
         */
        $applicationManager = $this->getContainer()->get(ApplicationManager::class);;


        /** @var bool $setupCluster */
        $setupCluster = $this->getContainer()->getParameter('setup_cluster');

       /** @var bool $setupLdap */
        $setupLdap = $this->getContainer()->getParameter('setup_ldap');

       /** @var bool $setupDemoApplication */
        $setupDemoApplication = $this->getContainer()->getParameter('setup_demo');


        /** @var bool $stripeEnabled */
        $stripeEnabled = $this->getContainer()->getParameter('stripe_enabled');

        /** @var bool $purge */
        $purge = $input->getOption("purge") === true ?: false;
        //get usersCount
        $usersCount = sizeof($userService->getUsers());
        if(!$purge && $usersCount > 0) {
            $this->display($output, "Entries already exist, nothing to do !");
            return 0;
        }

        if ($stripeEnabled === true) {
            $this->display($output, "Deleting Stripe plans");
            $planService->deleteAllPlans();
        }

        //delete all applications
       if ($purge) {
        $applications = $applicationManager->getAll();
            foreach ($applications as $application) {
                $applicationService->deleteApplication($application->getId());
            }
        }

        $this->loadFixtures($output, $purge);
       
        if ($setupLdap){
            $this->display($output, "Creating LDAP entries for admin user");
            $ldap->createAdminUser();
	    }

        $demoApplications = $applicationService->listDemoApplications();

      
        if ($setupCluster){
            $this->display($output, "Initializing Elasticsearch Cluster Setup");
            $es->createConfig();
            $es->createLeadwireRole();
            $es->deletePolicy("hot-warm-delete-policy");
            $es->deletePolicy("rollover-hot-warm-delete-policy");
            $es->createPolicy();
            $es->createRolloverPolicy();
            $es->createLeadwireRolesMapping();
            foreach ($demoApplications as $application) {
                $es->initIndexTemplate($application);
	        }
	    }

        if ($setupDemoApplication) {
	        $this->display($output, "Initializing Demo Application");
            /** @var Application $application */
            foreach ($demoApplications as $application) {
                $sharedIndex = "staging-" . $application->getSharedIndex();
                $appIndex = "staging-" . $application->getApplicationIndex();
                $patternIndex = "*-staging-" . $application->getName() . "-*";
                $watechrIndex = "staging-" . $application->getApplicationWatcherIndex();
            
                $es->createTenant($appIndex);
                $es->createTenant($sharedIndex);
                $es->createTenant($watechrIndex);

                $es->createIndexTemplate($application, $applicationService->getActiveApplicationsNames(), "staging");
                $kibana->loadIndexPatternForApplication(
                    $application,
                    $appIndex,
                    "staging"
                );

                $kibana->createApplicationDashboards($application, "staging");

                $kibana->loadIndexPatternForApplication(
                    $application,
                    $sharedIndex,
                    "staging"
                );

                $kibana->loadDefaultIndex($appIndex, 'default');
                $kibana->makeDefaultIndex($appIndex, 'default');

                $kibana->loadDefaultIndex($sharedIndex, 'default');
                $kibana->makeDefaultIndex($sharedIndex, 'default');
                
                //create role for application
                $es->createRole("staging", $application->getName(), array($patternIndex), array($sharedIndex, $appIndex), array("kibana_all_read"), false, false);
                $es->createRoleMapping("staging", $application->getName(), 'demo',  false, false);

                //create role for watcher
                $es->createRole("staging", $application->getName(), array(), array($watechrIndex), array("kibana_all_write"), true, true);
                $es->createRoleMapping("staging", $application->getName(), 'demo',  true, true);
            }
        }

        if ($stripeEnabled === true) {
            $this->display($output, "Creating Stripe Plans with new Data");
            $planService->createDefaultPlans();
        }

        $string = file_get_contents("./app/Resources/templates/v7.6.1/pipelines/pipelines.json");

        $json_a = json_decode($string, true);

        foreach ($json_a as $name => $body) {
            $es->addPipline($name, $body);
        }

        return 0;
    }

    
         
         
    private function loadFixtures($output, $purge)
    {
        //if ($purge === false) {
        //    return;
        //}

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
        $executor->execute($fixtures, !$purge);
    }

    private function display($output, $message)
    {
        $output->writeln(sprintf('  <comment>></comment> <info>%s</info>', $message));
    }
}
