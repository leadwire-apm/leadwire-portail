<?php

namespace AppBundle\DataFixtures\MongoDB;

use AppBundle\Document\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class UserFixture extends AbstractFixture implements OrderedFixtureInterface
{
    const USER_ALI = "user-ali";

    public function load(ObjectManager $manager)
    {
        // $plan = $this->getReference(PlanFixture::BASIC_PLAN);

        $user = new User();
        $user
            ->setUsername("aturki")
            ->setActive(true)
            ->setRoles([User::ROLE_SUPER_ADMIN, User::DEFAULT_ROLE])
            ->setUuid(Uuid::uuid1())
            ->setAvatar("https://avatars3.githubusercontent.com/u/13443190?v=4")
            ->setName("Ali Turki")
            ->setEmailValid(true)
            ->setLocked(false)
            ->setCompany("ATS")
            ->setContact("33###0695683303")
            ->setContactPreference("Email")
            ->setEmail("aturki@ats-digital.com")
            ->setPlan(null);

        $manager->persist($user);
        $manager->flush();

        $this->addReference(self::USER_ALI, $user);
    }

    public function getOrder()
    {
        return 110;
    }
}
