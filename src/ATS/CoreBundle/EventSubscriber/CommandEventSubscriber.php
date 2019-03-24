<?php declare (strict_types = 1);

namespace ATS\CoreBundle\EventSubscriber;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Stopwatch\Stopwatch;
use ATS\CoreBundle\Service\Util\StringFormatter;

/**
 * CommandEventSubscriber
 *
 * @author Wajih WERIEMI <wweriemi@ats-digital.com>
 */
class CommandEventSubscriber implements EventSubscriberInterface
{
    const SUCCESS = 0;

    /**
     * @var StopWatch
     */
    private $stopWatch;

    /**
     * Constructor
     *
     * @param StopWatch $stopWatch
     */
    public function __construct(StopWatch $stopWatch)
    {
        $this->stopWatch = $stopWatch;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => [
                ['onCommand', 0],
            ],
            ConsoleEvents::ERROR => [
                ['onError', 0],
            ],
            ConsoleEvents::TERMINATE => [
                ['onTerminate', 0],
            ],
        ];
    }

    /**
     * COMMAND event fired
     *
     * @param ConsoleCommandEvent $event
     *
     * @return void
     */
    public function onCommand(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();
        if ($command === null || $this->notSupportCommand($command->getName()) === true) {
            return;
        }
        $this->stopWatch->start($command->getName());
    }

    /**
     * ERROR event fired
     *
     * @param ConsoleErrorEvent $event
     *
     * @return void
     */
    public function onError(ConsoleErrorEvent $event)
    {
        $command = $event->getCommand();
        if ($command === null
            || $this->notSupportCommand($command->getName()) === true
            || $this->stopWatch->isStarted($command->getName()) === false) {
            return;
        }

        $this->stopWatch->stop($command->getName());
    }

    /**
     * TERMINATE event fired
     *
     * @param ConsoleTerminateEvent $event
     *
     * @return void
     */
    public function onTerminate(ConsoleTerminateEvent $event)
    {
        $command = $event->getCommand();
        if ($command === null
            || $event->getExitCode() !== self::SUCCESS
            || $this->notSupportCommand($command->getName()) === true) {
            return;
        }

        $output = $event->getOutput();
        $output->writeln(
            sprintf(
                '%sCommand <info>%s</info> succeeded',
                PHP_EOL,
                $command->getName()
            )
        );

        $watcher = $this->stopWatch->stop($command->getName());
        $output->writeln(
            sprintf(
                'Execution time: <comment>%s</comment>',
                StringFormatter::humanizeDuration($watcher->getDuration())
            )
        );
        $output->writeln(
            sprintf(
                'Memory Usage: <comment>%s</comment>',
                StringFormatter::humanizeMemorySize($watcher->getMemory())
            )
        );
    }

    /**
     * Returns the StopWatch object
     *
     * @return StopWatch
     */
    public function getStopWatch()
    {
        return $this->stopWatch;
    }

    /**
     * not support command
     *
     * @param string $command
     *
     * @return bool
     */
    private function notSupportCommand($command)
    {
        $notSupportCommands = ['about', 'help', 'list'];

        return in_array($command, $notSupportCommands, true);
    }
}
