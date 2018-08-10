<?php declare(strict_types=1);

namespace ATS\AnalyticsBundle\Service\Handler;

use ATS\AnalyticsBundle\Event\LoginEvent;
use ATS\AnalyticsBundle\Document\LoginStatistic;
use ATS\AnalyticsBundle\Event\BaseAnalyticsEvent;
use ATS\AnalyticsBundle\Manager\LoginStatisticManager;
use ATS\AnalyticsBundle\Service\Handler\EventHandlerInterface;

class LoginEventHandler implements EventHandlerInterface
{
    /**
     * @var LoginStatisticManager
     */
    private $lsManager;

    public function __construct(LoginStatisticManager $lsManager)
    {
        $this->lsManager = $lsManager;
    }

    public function handleEvent(BaseAnalyticsEvent $event)
    {
        $loginStatistic = new LoginStatistic();
        $loginStatistic
            ->setUsername($event->getUser()->getUsername())
            ->setDate(new \DateTime())
            ->setStatus($event->getLoginStatus())
        ;

        $this->lsManager->update($loginStatistic);
    }

    public function canHandleEvent(BaseAnalyticsEvent $event)
    {
        if ($event instanceof LoginEvent) {
            return true;
        }

        return false;
    }
}
