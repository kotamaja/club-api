<?php

namespace App\DataFixtures;

use App\Entity\ConnectionUser;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ConnectionUserFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {
        $user = new ConnectionUser("user@test.com");

        $this->addReference("connected user", $user);

        $manager->persist($user);

        $manager->flush();
    }


}
