<?php declare (strict_types = 1);

namespace AppBundle\Command;

use AppBundle\Service\CuratorService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class GenerateCuratorFileCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName("leadwire:curator:generate")
            ->setDescription("Generates YAML mapping for Curator")
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output file', null);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $dump = '';
        /** @var CuratorService $sg */
        $curator = $this->getContainer()->get(CuratorService::class);
        /** @var ?string $outputFile */
        $outputFile = $input->getOption('output');

        $dump = $curator->generateConfig();

        if ($outputFile === null) {
            $output->writeln($dump);
        } else {
            $fs = new Filesystem();
            $fs->dumpFile($outputFile, $dump);
        }
    }
}
