<?php

namespace App\DataFixtures;

use App\Entity\Club;
use App\Entity\ClubMembershipGroup;
use App\Entity\ClubMembershipGroupMembership;
use App\Entity\Membership;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ClubMembershipGroupMembershipFixtures extends Fixture implements DependentFixtureInterface
{

    public function getDependencies(): array
    {
        return [
            ClubMembershipGroupFixtures::class,
            MembershipFixtures::class,
        ];
    }


    private function create(ObjectManager $manager, string $groupRef, string $membershipRef, string $notes): ClubMembershipGroupMembership
    {
        $entity = new ClubMembershipGroupMembership();
        $group = $this->getReference($groupRef, ClubMembershipGroup::class);
        $entity->setGroup($group);
        $membership = $this->getReference($membershipRef, Membership::class);
        $entity->setMembership($membership);
        $entity->setNotes($notes);

        $manager->persist($entity);
        return $entity;
    }


    public function load(ObjectManager $manager): void
    {

        $this->create($manager ,"Club Group 1", "yves", "");
        $this->create($manager ,"Club Group 1", "marie", "");
        $this->create($manager ,"Club Group 1", "serge", "");


        $manager->flush();





    }
}
