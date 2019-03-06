<?php

namespace AppBundle\DataFixtures\MongoDB;

use ATS\PaymentBundle\Document\Plan;
use ATS\PaymentBundle\Document\PricingPlan;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class PlanFixture extends AbstractFixture implements OrderedFixtureInterface
{
    const BASIC_PLAN = "basic-plan";

    public function load(ObjectManager $manager)
    {
        $first = new Plan();
        $first->setName("BASIC")
            ->setIsCreditCard(false)
            ->setDiscount(0)
            ->setPrice(0)
            ->setMaxTransactionPerDay(10000)
            ->setRetention(1);

        $manager->persist($first);

        $second = new Plan();
        $second->setName("STANDARD")
            ->setIsCreditCard(true)
            ->setDiscount(15)
            ->setPrice(71)
            ->setMaxTransactionPerDay(100000)
            ->setRetention(7);

        $pricing = new PricingPlan();
        $pricing->setName("monthly");
        $pricing->setToken("STANDARD-month");
        $second->addPrice($pricing);

        $pricing = new PricingPlan();
        $pricing->setName("yearly");
        $pricing->setToken("STANDARD-year");
        $second->addPrice($pricing);

        $manager->persist($second);

        $third = new Plan();
        $third->setName("PREMIUM")
            ->setIsCreditCard(true)
            ->setDiscount(15)
            ->setPrice(640)
            ->setMaxTransactionPerDay(1000000)
            ->setRetention(15);

        $pricing = new PricingPlan();
        $pricing->setName("monthly");
        $pricing->setToken("PREMIUM-month");
        $third->addPrice($pricing);

        $pricing = new PricingPlan();
        $pricing->setName("yearly");
        $pricing->setToken("PREMIUM-year");
        $third->addPrice($pricing);

        $manager->persist($third);

        $manager->flush();

        $this->addReference(self::BASIC_PLAN, $first);
    }

    public function getOrder()
    {
        return 100;
    }
}
