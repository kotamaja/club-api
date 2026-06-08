<?php

namespace App\DataFixtures;

use App\Entity\Club;
use App\Entity\ClubMembershipGroup;
use App\Factory\ClubMembershipGroupFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ClubMembershipGroupFixtures extends Fixture implements DependentFixtureInterface
{

    public function getDependencies(): array
    {
        return [
            ClubFixtures::class,
        ];
    }

    private function create(ObjectManager $manager, string $name, string $description, string $clubRef): ClubMembershipGroup
    {

        $club = $this->getReference($clubRef, Club::class);

        $group = ClubMembershipGroup::create(club: $club, name: $name, description: $description);

        $this->addReference($name, $group);

        $manager->persist($group);
        return $group;
    }


    public function load(ObjectManager $manager): void
    {

        $this->create($manager, "Club Group 1", "une petite description", "Rowing Club Lausanne");
        $this->create($manager, "Club Group 2", "une petite description", "Rowing Club Lausanne");
        $this->create($manager, "Club Group 3", "une petite description", "Rowing Club Lausanne");
        $this->create($manager, "Club Group 4", "une petite description", "Rowing Club Lausanne");

        $manager->flush();

    }
}
