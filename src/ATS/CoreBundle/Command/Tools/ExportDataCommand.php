<?php declare(strict_types=1);

namespace ATS\CoreBundle\Command\Tools;

use Symfony\Component\Console\Helper\Table;
use ATS\CoreBundle\Command\Base\BaseCommand;
use ATS\CoreBundle\Service\Exporter\Exporter;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ExportDataCommand extends BaseCommand
{
    private $exporter;

    public function __construct(Exporter $exporter)
    {
        $this->exporter = $exporter;

        parent::__construct();
    }

    protected function configure()
    {
        $supportedFormats = Exporter::getSupportedFormats();

        $this
            ->setName('ats:core:export-data')
            ->setDescription('Exports data')
            ->addArgument('document', InputArgument::REQUIRED, 'The document to be used', null)
            ->addOption('filter', 'f', InputOption::VALUE_REQUIRED, 'Filter to be used for prefetch', null)
            ->addOption(
                'schema',
                's',
                InputOption::VALUE_REQUIRED,
                'Export Schema. Comma separated field descriptors',
                null
            )
            ->addOption(
                'format',
                'fo',
                InputOption::VALUE_REQUIRED,
                'Export format. One of [' . implode(',', $supportedFormats) . ']',
                null
            )
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output file name', null)
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command exports data from the database for a given Document class:

  <info>php %command.full_name% AppBundle:Product
    --filter 'product.price=50'
    --schema 'product.category,product.name,product.count'
    --format=csv</info>
EOF
            )
        ;
    }

    protected function doExecute()
    {
        $documentClass = str_replace(":", "\\Document\\", $this->input->getArgument('document'));
        $filter = $this->input->getOption('filter');
        $schema = $this->input->getOption('schema');
        $format = $this->input->getOption('format');
        $outputFile = $this->input->getOption('output');

        if (!in_array($format, Exporter::getSupportedFormats())) {
            throw new \Exception("Unsupported export format ($format)");
        }

        $exported = $this->exporter
            ->setFormat($format)
            ->setEntity($documentClass)
            ->setFilter($filter)
            ->setSchema(explode(',', $schema))
            ->export()
        ;

        if ($outputFile) {
            $this->exporter->getFile($outputFile);
        } else {
            $lines = $this->exporter->getRawData();
            $table = new Table($this->output);
            $table->setHeaders(explode(',', $schema));
            $table->setRows($lines);
            $table->render();
        }
    }
}
