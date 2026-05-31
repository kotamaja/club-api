<?php

namespace App\DataFixtures;

use App\Entity\Club;
use App\Entity\Organization;
use App\Factory\ClubFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ClubFixtures extends Fixture implements DependentFixtureInterface
{

    private function create(ObjectManager $manager, string $name, Organization $organization): Club
    {

        $club = Club::create($name, $organization);
        $club->setName($name);
        $manager->persist($club);
        $this->addReference(sprintf("%s", $name), $club);
        return $club;
    }


    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create('fr_CH');

        $organization1 = $this->getReference("Association Vaudoise Aviron", Organization::class);

        $this->create($manager, "Rowing Club Lausanne", $organization1);
        $this->create($manager, "Lausanne Sport Aviron", $organization1);

        $organization2 = $this->getReference("Association Jurassienne Aviron", Organization::class);
        $this->create($manager, "Rowing Club Delemont", $organization2);


        $manager->flush();


        $clubs = ClubFactory::createMany(5, ['organization' => $organization1,]);
        $i = 1;
        foreach ($clubs as $club) {
            $this->addReference(sprintf("ref-%s", $i), $club);
            $i++;
        }
    }

    public function getDependencies(): array
    {
        return [
            OrganizationFixtures::class,
        ];
    }
}
