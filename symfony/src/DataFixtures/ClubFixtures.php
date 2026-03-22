<?php

namespace App\DataFixtures;

use App\Entity\Club;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ClubFixtures extends Fixture
{

    private function create(ObjectManager $manager, string $name): Club {
        $club = new Club();
        $club->setName($name);
        $manager->persist($club);
        $this->addReference(sprintf("%s", $name), $club);
        return $club;
    }


    public function load(ObjectManager $manager): void
    {


       $this->create($manager, "Rowing Club Lausanne");
       $this->create($manager, "Lausanne Sport Aviron");

        $manager->flush();
    }
}
