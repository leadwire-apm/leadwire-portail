<?php

namespace AppBundle\Command;

use AppBundle\Service\LdapService;
use ATS\PaymentBundle\Service\PlanService;
use Doctrine\Common\DataFixtures\Executor\MongoDBExecutor;
use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class InstallCommand extends ContainerAwareCommand
{
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
        /** @var LdapService $ldap */
        $ldap = $this->getContainer()->get(LdapService::class);
        /** @var PlanService $planService */
        $planService = $this->getContainer()->get(PlanService::class);
        $this->loadFixtures($output);
        $ldap->createDemoApplicationsEntries();
        $planService->createDefaultPlans();

        return 0;
    }

    private function loadFixtures($output)
    {
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
}
