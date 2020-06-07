<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use DateTime;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setEmail('user' . $i . '@gmail.com');
            $user->setRoles(['ROLE_USER']);
            $user->setPassword('user' . $i);
            $user->setIsActive(1);
            $user->setDate(new DateTime('now'));
            $manager->persist($user);
        }

        $manager->flush();
    }
}
