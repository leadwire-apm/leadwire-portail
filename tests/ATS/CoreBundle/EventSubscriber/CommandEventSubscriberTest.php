<?php

namespace Tests\ATS\CoreBundle\EventSubscriber;

use ATS\CoreBundle\Command\Tools\Doctrine\CheckReferenceCommand;
use ATS\CoreBundle\EventSubscriber\CommandEventSubscriber;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Stopwatch\Stopwatch;

class CommandEventSubscriberTest extends KernelTestCase
{
    public function testOnCommand()
    {
        $kernel = self::bootKernel();
        $managerRegistry = $kernel->getContainer()->get('doctrine_mongodb');
        $application = new Application($kernel);
        $command = new CheckReferenceCommand($managerRegistry);
        $input = new ArgvInput();
        $output = new ConsoleOutput();
        $event = new ConsoleCommandEvent($command, $input, $output);
        $stopWatch = new StopWatch();
        $eventSubscriber = new CommandEventSubscriber($stopWatch);
        $eventSubscriber->onCommand($event);
        $stopWatch = $eventSubscriber->getStopWatch();
        $this->assertTrue($stopWatch->isStarted(CheckReferenceCommand::CMD_NAME));

        $command = new HelpCommand();
        $event = new ConsoleCommandEvent($command, $input, $output);
        $stopWatch = new StopWatch();
        $eventSubscriber = new CommandEventSubscriber($stopWatch);
        $eventSubscriber->onCommand($event);
        $stopWatch = $eventSubscriber->getStopWatch();
        $this->assertFalse($stopWatch->isStarted($command->getName()));
    }

    public function testOnError()
    {
        $kernel = self::bootKernel();
        $managerRegistry = $kernel->getContainer()->get('doctrine_mongodb');
        $application = new Application($kernel);
        $command = new CheckReferenceCommand($managerRegistry);
        $input = new ArgvInput();
        $output = new ConsoleOutput();
        $stopWatch = new StopWatch();
        $eventSubscriber = new CommandEventSubscriber($stopWatch);
        $event = new ConsoleCommandEvent($command, $input, $output);
        $eventSubscriber->onCommand($event);
        $event = new ConsoleErrorEvent($input, $output, new \Exception("Command error"), $command);
        $eventSubscriber->onError($event);
        $this->assertFalse($stopWatch->isStarted($command->getName()));

        $event = new ConsoleErrorEvent($input, $output, new \Exception("Command error"), null);
        $eventSubscriber->onError($event);
        $this->assertFalse($stopWatch->isStarted($command->getName()));
    }

    /**
     * @uses ATS\CoreBundle\Service\Util\StringFormatter::humanizeDuration
     * @uses ATS\CoreBundle\Service\Util\StringFormatter::humanizeMemorySize
     * @return void
     */
    public function testOnTerminate()
    {
        $kernel = self::bootKernel();
        $managerRegistry = $kernel->getContainer()->get('doctrine_mongodb');
        $application = new Application($kernel);
        $command = new CheckReferenceCommand($managerRegistry);
        $input = new ArgvInput();
        $output = new ConsoleOutput();
        $stopWatch = new StopWatch();
        $eventSubscriber = new CommandEventSubscriber($stopWatch);
        $event = new ConsoleCommandEvent($command, $input, $output);
        $eventSubscriber->onCommand($event);
        $event = new ConsoleTerminateEvent($command, $input, $output, 0);
        $eventSubscriber->onTerminate($event);
        $this->assertFalse($eventSubscriber->getStopWatch()->isStarted(CheckReferenceCommand::CMD_NAME));

        $event = new ConsoleCommandEvent($command, $input, $output);
        $eventSubscriber->onCommand($event);
        $event = new ConsoleTerminateEvent($command, $input, $output, 113);
        $eventSubscriber->onTerminate($event);
        $this->assertTrue($eventSubscriber->getStopWatch()->isStarted(CheckReferenceCommand::CMD_NAME));
    }

    public function testGetSubscribedEvents()
    {
        $events = CommandEventSubscriber::getSubscribedEvents();
        $this->assertCount(3, $events);
        $this->assertArrayHasKey(ConsoleEvents::COMMAND, $events);
        $this->assertArrayHasKey(ConsoleEvents::ERROR, $events);
        $this->assertArrayHasKey(ConsoleEvents::TERMINATE, $events);
    }
}
