<?php

namespace App\DataFixtures;

use App\Entity\Club;
use App\Entity\Person;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PersonFixtures extends Fixture
{

    private function create(ObjectManager $manager, string $firstname, string $lastname): Person {
        $person = new Person();
        $person->setFirstname($firstname);
        $person->setLastname($lastname);
        $person->setEmail(sprintf("%s.%s@test.com", $firstname, $lastname));
        $manager->persist($person);
        $this->addReference(sprintf("%s-%s", $firstname,$lastname), $person);
        return $person;
    }


    public function load(ObjectManager $manager): void
    {


        $this->create($manager, "yves", "a");
        $this->create($manager, "marie", "a");
        $this->create($manager, "serge", "a");
        $this->create($manager, "anna", "a");
        $this->create($manager, "monica", "a");

        $manager->flush();
    }
}
