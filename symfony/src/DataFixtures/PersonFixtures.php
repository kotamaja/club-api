<?php

namespace App\DataFixtures;

use App\Entity\Club;
use App\Entity\Organization;
use App\Entity\Person;
use App\Factory\PersonFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PersonFixtures extends Fixture implements DependentFixtureInterface
{

    private function create(ObjectManager $manager, string $firstname, string $lastname, Organization $organization): Person {

        $person = Person::create($firstname, $lastname, sprintf("%s.%s@test.com", $firstname, $lastname), $organization);
        $manager->persist($person);
        $this->addReference(sprintf("%s-%s", $firstname,$lastname), $person);
        return $person;
    }


    public function load(ObjectManager $manager): void
    {
        $organization1 = $this->getReference("Association Vaudoise Aviron", Organization::class);

        $this->create($manager, "yves", "a", $organization1);
        $this->create($manager, "daniel", "a", $organization1);
        $this->create($manager, "marie", "a", $organization1);
        $this->create($manager, "serge", "a", $organization1);
        $this->create($manager, "anna", "a", $organization1);
        $this->create($manager, "monica", "a", $organization1);

        $organization2 = $this->getReference("Association Jurassienne Aviron", Organization::class);
        $this->create($manager, "yves", "b", $organization2);
        $this->create($manager, "olivier", "b", $organization2);

        $manager->flush();

        $people = PersonFactory::new()->forOrganization($organization1)->many(200)->create();
        $i = 1;
        foreach ($people as $person) {
            $this->addReference(sprintf("ref-%s", $i), $person);
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
