<?php

namespace App\DataFixtures;

use App\Entity\Organization;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class OrganizationFixtures extends Fixture
{
    public function __construct(private SluggerInterface $slugger)
    {
    }


    private function create(ObjectManager $manager, string $name): Organization
    {
        $organization = new Organization($name, $this->slugger->slug($name)->lower()->toString());
        $this->addReference($name, $organization);
        $manager->persist($organization);
        return $organization;
    }


    public function load(ObjectManager $manager): void
    {
        $this->create($manager, "Association Vaudoise Aviron");
        $this->create($manager, "Association Jurassienne Aviron");
        $manager->flush();

    }
}
