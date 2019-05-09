<?php

namespace AppBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixture extends AbstractFixture implements OrderedFixtureInterface
{

    public function load(ObjectManager $manager)
    {
    }

    public function getOrder()
    {
        return 110;
    }
}
