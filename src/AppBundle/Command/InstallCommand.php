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
        $output->writeln("This command is deprecacted and has been replaced with Doctrine fixutres");

        return 0;
    }
}
