<?php

namespace Tests\ATS\AnalyticsBundle\Listener;

use ATS\AnalyticsBundle\Document\LoginStatistic;
use ATS\AnalyticsBundle\Event\LoginEvent;
use ATS\UserBundle\Document\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AnalyticsEventListenerTest extends WebTestCase
{

    private $container;

    public function setUp()
    {
        $kernel = static::bootKernel();
        $this->container = $kernel->getContainer();
    }

    public function testLoginEvent()
    {
        $event = new LoginEvent();
        $event->setUser($this->getDummyUser());
        $event->setLoginStatus('SUCCESS');

        $this->container->get('event_dispatcher')->dispatch(LoginEvent::NAME, $event);

        $stat = $this->container
            ->get('doctrine_mongodb')
            ->getManager()
            ->getRepository(LoginStatistic::class)
            ->findOneBy(['username' => 'dummy']);

        $this->assertEquals('dummy', $stat->getUsername());
    }

    private function getDummyUser()
    {
        return (new User())
            ->setUsername('dummy')
        ;
    }
}
