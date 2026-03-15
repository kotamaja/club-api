<?php

namespace App\Mapper\CustomMapper\PersonRelationship;

use App\Dto\PersonRelationship\PersonRelationshipPatchDto;
use App\Entity\PersonRelationship;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;

#[Maps(source: PersonRelationshipPatchDto::class, target: PersonRelationship::class)]
final class PersonRelationshipPatchDtoToPersonRelationshipMapper implements CustomMapperInterface
{
    public function map(mixed $source, mixed $target = null): mixed
    {

        if (!$source instanceof PersonRelationshipPatchDto) {
            throw new \LogicException('Invalid mapper usage.');
        }
        $personRelationship = $target instanceof PersonRelationship ? $target : new PersonRelationship();


        if ($source->isTypeProvided()) {
            $personRelationship->setType($source->getType());
        }
        if ($source->isEmergencyContactProvided()) {
            $personRelationship->setEmergencyContact((bool) $source->getIsEmergencyContact());
        }

        return $personRelationship;
    }
}
