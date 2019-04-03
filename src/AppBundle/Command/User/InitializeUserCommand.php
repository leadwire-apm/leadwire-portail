<?php declare (strict_types = 1);

namespace AppBundle\Command\User;

use AppBundle\Document\User;
use AppBundle\Manager\UserManager;
use AppBundle\Service\LdapService;
use AppBundle\Service\KibanaService;
use AppBundle\Service\ApplicationService;
use AppBundle\Service\SearchGuardService;
use AppBundle\Service\ElasticSearchService;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class InitializeUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName("leadwire:user:init")
            ->setDescription("Initializes a user in LDAP, ES & Kibana")
            ->addOption("username", "u", InputOption::VALUE_REQUIRED, 'The username')
            ->addOption("with-ldap", "l", InputOption::VALUE_NONE, "Initialize LDAP entries")
            ->addOption("with-kibana", "k", InputOption::VALUE_NONE, "Initialize ES & Kibana entries")
            ->addOption("with-sg", "s", InputOption::VALUE_NONE, "Initialize SearchGuard entries");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $userManager = $this->getContainer()->get(UserManager::class);

        /** @var LdapService $ldap */
        $ldap = $this->getContainer()->get(LdapService::class);
        $applicationService = $this->getContainer()->get(ApplicationService::class);
        $es = $this->getContainer()->get(ElasticSearchService::class);
        $kibana = $this->getContainer()->get(KibanaService::class);
        $sg = $this->getContainer()->get(SearchGuardService::class);

        /** @var ?bool $withLdap */
        $withLdap = $input->getOption("with-ldap");
        /** @var ?bool $withKibana */
        $withKibana = $input->getOption("with-kibana");
        /** @var ?bool $withSg */
        $withSg = $input->getOption("with-sg");
        /** @var string $username */
        $username = $input->getOption("username");

        /** @var ?User $user */
        $user = $userManager->getOneBy(['username' => $username]);
        if ($user === null) {
            throw new \Exception("User with username $username not found");
        }

        if ($withLdap === true) {
            $output->write("<info>Creating LDAP User entries </info>");
            $ldap->createNewUserEntries($user);
            $output->write(".");
            $ldap->registerDemoApplications($user);
            $output->write(".");
            $output->writeln("Done");
        }

        $applicationService->registerDemoApplications($user);
        if ($withKibana === true) {
            $output->write("<info>Loading Kibana Index Patterns </info>");
            $es->deleteIndex($user->getUserIndex());
            $output->write(".");
            $kibana->loadIndexPatternForUserTenant($user);
            $output->write(".");

            $es->deleteIndex($user->getAllUserIndex());
            $output->write(".");
            $kibana->loadIndexPatternForAllUser($user);
            $output->write(".");
            $kibana->createAllUserDashboard($user);
            $output->write(".");
            $output->writeln("Done");
        }

        if ($withSg === true) {
            $output->write("<info>Updaing SearchGuard Configuration </info>");
            $sg->updateSearchGuardConfig();
            $output->write(".");
            $output->writeln("Done");
        }
    }
}
