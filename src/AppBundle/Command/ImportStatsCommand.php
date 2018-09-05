<?php
namespace AppBundle\Command;

use AppBundle\Document\Stat;
use AppBundle\Manager\AppManager;
use AppBundle\Service\StatService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

class ImportStatsCommand extends Command
{
    /**
     * @var StatService
     */
    private $statService;

    /**
     * @var AppManager
     */
    private $appManager;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(StatService $statService, AppManager $appManager, ManagerRegistry $managerRegistry)
    {
        $this->statService = $statService;
        $this->appManager = $appManager;
        $this->managerRegistry = $managerRegistry;

        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setName('leadwire:import:stats')
            ->setDescription('Import CSV file to Database')
            ->addArgument('file', InputArgument::REQUIRED, "CSV file to import")
            ->setHelp('Import CSV file to Database.
CSV file must have a header in first line. It should loik like:
app_uuid;jour;nb_tx');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        if (is_file($file)) {
            $stats = [];
            $apps = [];
            $row = 0;

            if (($handle = fopen($file, "r")) !== false) {
                while (($data = fgetcsv($handle, 0, ";")) !== false) {
                    $num = count($data);
                    $row++;
                    for ($c = 1; $c < $num; $c++) {
                        if (!isset($apps[$data[0]])) {
                            $apps[$data[0]] = $this->appManager->getOneBy(['uuid' => $data[0]]);
                            if (!$apps[$data[0]]) {
                                $output->writeln(
                                    "<fg=red> App with uuid = '"
                                    . $data[0]
                                    . "' was not found on our databases </>"
                                );
                                continue;
                            }
                        }
                        $timezone = new \DateTimeZone('Europe/London');
                        $date = \DateTime::createFromFormat('Ymd', $data[1], $timezone);
                        $stats[$row] = new Stat();
                        $stats[$row]->setDay($date)
                            ->setNbr($data[2])
                            ->setApp($apps[$data[0]]);

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
