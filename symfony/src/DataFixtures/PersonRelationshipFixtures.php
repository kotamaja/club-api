<?php

namespace App\DataFixtures;

use App\Entity\Person;
use App\Entity\PersonRelationship;
use App\Enum\RelationshipType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PersonRelationshipFixtures extends Fixture
{

    private function create(ObjectManager $manager, string $subjectRef, string $relatedPersonRef, RelationshipType $type, bool $emergencyContact): PersonRelationship {
        $relation = new PersonRelationship();
        $subject = $this->getReference($subjectRef, Person::class);
        $relatedPerson = $this->getReference($relatedPersonRef, Person::class);

        $relation->setSubject($subject);
        $relation->setRelatedPerson($relatedPerson);

        $relation->setType($type);

        $relation->setEmergencyContact($emergencyContact);

        $manager->persist($relation);
        return $relation;
    }


    public function load(ObjectManager $manager): void
    {


        $this->create($manager, "yves-a", "marie-a", RelationshipType::PARTNER, true );
        $this->create($manager, "yves-a", "serge-a", RelationshipType::LEGAL_GUARDIAN, false );
        $this->create($manager, "yves-a", "monica-a", RelationshipType::OTHER, false );
        $this->create($manager, "anna-a", "yves-a", RelationshipType::PARENT, true );


        $manager->flush();

    }
}
