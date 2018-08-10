<?php declare (strict_types = 1);

namespace ATS\CoreBundle\Command\Base;

use Monolog\Logger;
use Symfony\Component\Stopwatch\Stopwatch;
use ATS\CoreBundle\Service\Util\StringFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class BaseCommand extends ContainerAwareCommand
{

    const OK = 0;
    const GENERAL_ERROR = 1;
    const CANNOT_EXECUTE = 126;
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var Logger
     */
    protected $logger;

    abstract protected function doExecute();

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->logger = $this->getContainer()->get('logger');

        $stopwatch = new Stopwatch();
        $stopwatch->start($this->getName());

        try {
            $this->doExecute();
            $this->info('Command executed successfully');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
        } finally {
            $event = $stopwatch->stop($this->getName());
            $this->info(
                'Execution Time: ' . StringFormatter::humanizeDuration($event->getDuration())
            );

            $this->info(
                'Memory Usage: ' . StringFormatter::humanizeMemorySize($event->getMemory())
            );
        }
    }

    /**
     *
     * @param string $message
     * @param array  $context
     * @param string $level
     *
     */
    protected function log($message, $context = [], $level = 'info')
    {
        $context['command'] = $this->getName();

        $this->logger->log(
            $level,
            sprintf('%s', $message),
            $context
        );
    }

    protected function debug($msg, $context = [])
    {
        $this->log($msg, $context, 'debug');
    }

    protected function info($msg, $context = [])
    {
        $this->log($msg, $context, 'info');
    }

    protected function warning($msg, $context = [])
    {
        $this->log($msg, $context, 'warning');
    }

    protected function error($msg, $context = [])
    {
        $this->log($msg, $context, 'critical');
    }
}
