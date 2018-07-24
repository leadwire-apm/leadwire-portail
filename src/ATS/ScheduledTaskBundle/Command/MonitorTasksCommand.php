<?php declare (strict_types = 1);

namespace ATS\ScheduledTaskBundle\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;
use ATS\ScheduledTaskBundle\Document\Task;
use ATS\CoreBundle\Command\Base\BaseCommand;
use ATS\ScheduledTaskBundle\Service\TaskService;
use Symfony\Component\Console\Input\InputOption;

class MonitorTasksCommand extends BaseCommand
{
    const DEFAULT_INTERVAL = 1;
    const CMD_NAME = 'ats:task:monitor';

    /**
     * @var TaskService
     */
    private $taskService;

    public function __construct(TaskService $taskService, LoggerInterface $logger)
    {
        parent::__construct();
        $this->logger = $logger;
        $this->taskService = $taskService;
    }

    protected function configure()
    {
        $this->setName(self::CMD_NAME)
            ->addOption(
                'interval',
                'i',
                InputOption::VALUE_REQUIRED,
                'Monitoring interval',
                self::DEFAULT_INTERVAL
            )
            ->setDescription('Main monitor task');
    }

    protected function doExecute()
    {
        $pid = pcntl_fork();

        if ($pid == -1) {
            $this->output->writeln("<error>Could not fork process</error>");
            $this->shutdown();
        } elseif ($pid) {
            // Current Process --> exit
            exit(0);
        } else {
            // Child process
            $interval = $this->input->getOption('interval');

            while (true) {
                $activeTasks = $this->taskService->getActive();
                foreach ($activeTasks as $task) {
                    $now = (new \DateTime('now'));

                    $latestRun = $task->getLatestRun();

                    if (!$latestRun) {
                        $this->executeTask($task, $now);
                    } else {
                        $clone = clone $latestRun;
                        $clone->add(\DateInterval::createFromDateString($task->getInterval()));
                        if ($clone < $now) {
                            $this->executeTask($task, $now);
                        }
                    }
                }

                $this->debug(
                    'TasksMonitor',
                    [
                        'message' => "Sleeping for $interval seconds",
                    ]
                );

                sleep($interval);
            }
        }
    }

    private function executeTask(Task $task, \DateTime $now)
    {
        $this->info('Executing task', ['name' => $task->getName()]);

        $process = new Process(
            $task->getCommandLine(),
            null,
            null,
            null,
            $task->getTimeout(),
            null
        );

        $process->start();
        $process->wait();

        $task->setLatestRun($now);
        $this->taskService->update($task);
    }
}
