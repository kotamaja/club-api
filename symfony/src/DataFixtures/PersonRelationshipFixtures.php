<?php

namespace App\DataFixtures;

use App\Entity\Person;
use App\Entity\PersonContact;
use App\Enum\RelationshipType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PersonRelationshipFixtures extends Fixture implements DependentFixtureInterface
{

    private function create(ObjectManager $manager, string $subjectRef, string $relatedPersonRef, RelationshipType $type, bool $emergencyContact): PersonContact {
        $relation = new PersonContact();
        $subject = $this->getReference($subjectRef, Person::class);
        $relatedPerson = $this->getReference($relatedPersonRef, Person::class);

        $relation->setPerson($subject);
        $relation->setContactPerson($relatedPerson);

        $relation->setType($type);

        $relation->setIsEmergencyContact($emergencyContact);

        $manager->persist($relation);
        return $relation;
    }


    public function load(ObjectManager $manager): void
    {

        $this->create($manager, "yves-a", "marie-a", RelationshipType::LEGAL_GUARDIAN, true );
        $this->create($manager, "yves-a", "serge-a", RelationshipType::LEGAL_GUARDIAN, false );
        $this->create($manager, "yves-a", "monica-a", RelationshipType::OTHER, false );
        $this->create($manager, "anna-a", "yves-a", RelationshipType::PARENT, true );

        $manager->flush();

    }

    public function getDependencies(): array
    {
        return [
            PersonFixtures::class,
        ];
    }

}
