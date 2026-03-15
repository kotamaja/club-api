<?php

namespace App\Mapper\CustomMapper\PersonRelationship;

use App\Dto\PersonRelationship\PersonRelationshipCreateDto;
use App\Entity\PersonRelationship;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;

#[Maps(source: PersonRelationshipCreateDto::class, target: PersonRelationship::class)]
final class PersonRelationshipCreateDtoToPersonRelationshipMapper implements CustomMapperInterface
{
    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof PersonRelationshipCreateDto) {
            throw new \LogicException('Invalid mapper usage.');
        }
        $personRelationship = $target instanceof PersonRelationship ? $target : new PersonRelationship();


        $personRelationship->setType($source->type);
        $personRelationship->setEmergencyContact($source->isEmergencyContact);

        // Important: subject and relatedPerson are set in function beforePersist of PersonRelationshipCreateProcessor

        return $personRelationship;
    }
}
