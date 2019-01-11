<?php
namespace AppBundle\Command;

use DateTime;
use AppBundle\Document\Stat;
use AppBundle\Manager\ApplicationManager;
use Symfony\Component\Console\Command\Command;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportStatsCommand extends Command
{
    /**
     * @var ApplicationManager
     */
    private $applicationManager;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(ApplicationManager $applicationManager, ManagerRegistry $managerRegistry)
    {
        $this->applicationManager = $applicationManager;
        $this->managerRegistry = $managerRegistry;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('leadwire:import:stats')
            ->setDescription('Import CSV file to Database')
            ->addArgument('file', InputArgument::REQUIRED, "CSV file to import")
            ->setHelp(
                'Import CSV file to Database.
CSV file must have a header in first line. It should loik like:
app_uuid;jour;nb_tx'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var string $file */
        $file = $input->getArgument('file');

        if (is_file($file) === true) {
            $stats = [];
            $apps = [];
            $row = 0;

            $handle = fopen($file, "r");

            if ($handle !== false) {
                while (($data = fgetcsv($handle, 0, ";")) !== false) {
                    $num = is_array($data) ? count($data) : 0;
                    $row++;
                    for ($c = 1; $c < $num; $c++) {
                        if (false === isset($apps[$data[0]])) {
                            $apps[$data[0]] = $this->applicationManager->getOneBy(['uuid' => $data[0]]);
                            if ($apps[$data[0]] === false) {
                                $output->writeln(
                                    "<fg=red> App with uuid = '"
                                    . $data[0]
                                    . "' was not found on our databases </>"
                                );
                                continue;
                            }
                        }
                        $timezone = new \DateTimeZone('Europe/London');
                        $date = DateTime::createFromFormat('Ymd', $data[1], $timezone);
                        if ($date instanceof DateTime) {
                            $stats[$row]->setDay($date);
                        } else {
                            throw new \Exception(sprintf("Bad value for DateTime object [%s]", $data[1]));
                        }
                        $stats[$row] = new Stat();
                        $stats[$row]->setNbr($data[2])
                            ->setApplication($apps[$data[0]]);

                        $this->managerRegistry->getManager()->persist($stats[$row]);
                    }
                }
                fclose($handle);

                $this->managerRegistry->getManager()->flush();
                $output->writeln("<fg=green> Imported " . count($stats) . " elements to database.</>");
            }
        } else {
            $output->writeln("<fg=red>the file '$file' was not found</>");
        }
    }
}
