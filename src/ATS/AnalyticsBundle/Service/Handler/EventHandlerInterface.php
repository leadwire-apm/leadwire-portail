<?php declare(strict_types=1);

namespace ATS\AnalyticsBundle\Service\Handler;

use ATS\AnalyticsBundle\Event\BaseAnalyticsEvent;

interface EventHandlerInterface
{
    /**
     * @param BaseAnalyticsEvent $event
     */
    public function handleEvent(BaseAnalyticsEvent $event);

    /**
     * @param BaseAnalyticsEvent $event
     */
    public function canHandleEvent(BaseAnalyticsEvent $event);
}
