<?php

namespace App\DataFixtures;

use App\Entity\Club;
use App\Factory\ClubFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ClubFixtures extends Fixture
{

    private function create(ObjectManager $manager, string $ref, string $name): Club
    {
        $club = new Club();
        $club->setName($name);
        $manager->persist($club);
        $this->addReference(sprintf("%s", $name), $club);
        return $club;
    }


    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create('fr_CH');


        $this->create($manager, "Rowing Club Lausanne", "Rowing Club Lausanne");
        $this->create($manager, "Lausanne Sport Aviron", "Lausanne Sport Aviron");

        $manager->flush();


        $clubs = ClubFactory::createMany(5);
        $i = 1;
        foreach ($clubs as $club) {
            $this->addReference(sprintf("ref-%s", $i), $club);
            $i++;
        }
    }
}
