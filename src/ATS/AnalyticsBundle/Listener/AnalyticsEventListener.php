<?php declare(strict_types=1);

namespace ATS\AnalyticsBundle\Listener;

use ATS\AnalyticsBundle\Event\BaseAnalyticsEvent;
use ATS\AnalyticsBundle\Service\Handler\EventHandlerInterface;

class AnalyticsEventListener
{
    /**
     * @var EventHandlerInterface
     */
    protected $handlers;

    public function __construct()
    {
        $this->handlers = [];
    }

    /**
     * Front Event Handler. This is a single entry point for all tracking events
     *
     * @param BaseAnalyticsEvent $event
     */
    public function onTrackingEventTriggered(BaseAnalyticsEvent $event)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->canHandleEvent($event)) {
                $handler->handleEvent($event);
                break;
            }
        }
    }

    public function addHandler(EventHandlerInterface $handler)
    {
        $this->handlers[] = $handler;
    }
}
