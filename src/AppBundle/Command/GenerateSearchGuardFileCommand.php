<?php declare (strict_types = 1);

namespace AppBundle\Command;

use AppBundle\Service\SearchGuardService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class GenerateSearchGuardFileCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName("leadwire:sg:generate")
            ->setDescription("Generates YAML mapping for Search Guard")
            ->addArgument('which', InputArgument::REQUIRED, 'One of sg_roles, sg_roles_mapping')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output file', null);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $dump = '';
        /** @var SearchGuardService $sg */
        $sg = $this->getContainer()->get(SearchGuardService::class);

        /** @var ?string $outputFile */
        $outputFile = $input->getOption('output');
        /** @var string $which */
        $which = $input->getArgument('which');

        if ($which === 'sg_roles') {
            $dump = $sg->prepareConfig();
        } elseif ($which === 'sg_roles_mapping') {
            $dump = $sg->prepareMappingsConfig();
        } else {
            throw new \Exception("Wrong parameter $which");
        }

        if ($outputFile === null) {
            $output->writeln($dump);
        } else {
            $fs = new Filesystem();
            $fs->dumpFile($outputFile, $dump);
        }
    }
}
