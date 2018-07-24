<?php declare(strict_types=1);

namespace ATS\AnalyticsBundle\Event;

use ATS\AnalyticsBundle\Event\BaseAnalyticsEvent;
use ATS\UserBundle\Document\User;

class LoginEvent extends BaseAnalyticsEvent
{
    const NAME = "ats.analytics.login.event";

    /**
     * @var User
     */
    private $user;

    /**
     * @var string
     */
    private $loginStatus;


    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setLoginStatus($loginStatus)
    {
        $this->loginStatus = $loginStatus;
    }

    public function getLoginStatus()
    {
        return $this->loginStatus;
    }
}
