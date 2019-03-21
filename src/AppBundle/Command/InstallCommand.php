<?php

namespace AppBundle\Command;

use AppBundle\Service\LdapService;
use AppBundle\Document\Application;
use AppBundle\Service\KibanaService;
use AppBundle\Service\ApplicationService;
use Doctrine\ODM\MongoDB\DocumentManager;
use ATS\PaymentBundle\Service\PlanService;
use AppBundle\Service\ElasticSearchService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Doctrine\Common\DataFixtures\Executor\MongoDBExecutor;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;

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
        $ldap = $this->getContainer()->get(LdapService::class);
        /** @var ElasticSearchService $es */
        $es = $this->getContainer()->get(ElasticSearchService::class);
        /** @var KibanaService $kibana */
        $kibana = $this->getContainer()->get(KibanaService::class);
        /** @var PlanService $planService */
        $planService = $this->getContainer()->get(PlanService::class);
        /** @var ApplicationService $applicationService */
        $applicationService = $this->getContainer()->get(ApplicationService::class);

        /** @var bool $purge */
        $purge = $input->getOption("purge") === true ?: false;
        $this->display($output, "Deleting Stripe plans");
        $planService->deleteAllPlans();

        $this->loadFixtures($output, $purge);
        $this->display($output, "Creating LDAP entries for demo applications");
        $ldap->createDemoApplicationsEntries();
        $demoApplications = $applicationService->listDemoApplications();

        $this->display($output, "Initializing ES & Kibana");
        /** @var Application $application */
        foreach ($demoApplications as $application) {
            $es->deleteIndex("app_" . $application->getUuid());
            $es->createIndexTemplate($application, $applicationService->getActiveApplicationsNames());
            $es->createAlias($application->getName());

            $kibana->loadIndexPatternForApplication(
                $application,
                $application->getOwner(),
                'app_' . $application->getUuid()
            );

            $kibana->createApplicationDashboards($application, $application->getOwner());

            $es->deleteIndex("shared_" . $application->getUuid());

            $kibana->loadIndexPatternForApplication(
                $application,
                $application->getOwner(),
                'shared_' . $application->getUuid()
            );
        }

        $this->display($output, "Creating Stripe Plans with new Data");
        $planService->createDefaultPlans();

        return 0;
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
