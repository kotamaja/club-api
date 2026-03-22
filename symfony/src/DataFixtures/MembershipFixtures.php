<?php

namespace App\DataFixtures;

use App\Entity\Club;
use App\Entity\Membership;
use App\Entity\Person;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MembershipFixtures extends Fixture implements DependentFixtureInterface
{

    private function create(ObjectManager $manager, string $personRef, string $clubRef, \DateTimeImmutable $joinedAt, ?\DateTimeImmutable $endedAt): Membership
    {
        $membership = new Membership();
        $person = $this->getReference($personRef, Person::class);
        $club = $this->getReference($clubRef, Club::class);

        $membership->setPerson($person);
        $membership->setClub($club);

        $membership->setJoinedAt($joinedAt);

        $membership->setEndedAt($endedAt);

        $manager->persist($membership);
        return $membership;
    }


    public function load(ObjectManager $manager): void
    {


        $this->create($manager, "yves-a", "Rowing Club Lausanne", new DateTimeImmutable()->setDate(2020, 1, 30), null);
        $this->create($manager, sprintf("%s-%s", 'marie', 'a'), "Rowing Club Lausanne", new DateTimeImmutable()->setDate(2020, 1, 30), new DateTimeImmutable()->setDate(2021, 2, 25));
        $this->create($manager, sprintf("%s-%s", 'marie', 'a'), "Rowing Club Lausanne", new DateTimeImmutable()->setDate(2022, 1, 30), new DateTimeImmutable()->setDate(2024, 2, 25));
        $this->create($manager, sprintf("%s-%s", 'marie', 'a'), "Rowing Club Lausanne", new DateTimeImmutable()->setDate(2025, 1, 30), null);
        $this->create($manager, sprintf("%s-%s", 'serge', 'a'), "Rowing Club Lausanne", new DateTimeImmutable()->setDate(1980, 1, 30), new DateTimeImmutable()->setDate(1985, 2, 25));
        $this->create($manager, sprintf("%s-%s", 'serge', 'a'), "Rowing Club Lausanne", new DateTimeImmutable()->setDate(2000, 1, 30), new DateTimeImmutable()->setDate(2005, 2, 25));
        $this->create($manager, sprintf("%s-%s", 'serge', 'a'), "Rowing Club Lausanne", new DateTimeImmutable()->setDate(2010, 1, 30), new DateTimeImmutable()->setDate(2015, 2, 25));


        $manager->flush();

    }


    public function getDependencies(): array
    {
        return [
            PersonFixtures::class,
            ClubFixtures::class,
        ];
    }

}
