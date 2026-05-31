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

        $club = new Club($name, $organization);
        $club->setName($name);
        $manager->persist($club);
        $this->addReference(sprintf("%s", $name), $club);
        return $club;
    }


    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create('fr_CH');

        $organization = $this->getReference("Association Vaudoise Aviron", Organization::class);

        $this->create($manager, "Rowing Club Lausanne", $organization);
        $this->create($manager, "Lausanne Sport Aviron", $organization);

        $manager->flush();


        $clubs = ClubFactory::createMany(5, ['organization' => $organization,]);
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
