<?php

namespace App\Mapper\CustomMapper\PersonRelationship;

use App\Dto\PersonRelationship\PersonRelationshipViewDto;
use App\Entity\PersonRelationship;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;

#[Maps(source: PersonRelationship::class, target: PersonRelationshipViewDto::class)]
final class PersonRelationshipToPersonRelationshipViewDtoMapper implements CustomMapperInterface
{
    public function map(mixed $source,  mixed $target = null): object
    {
        if (!$source instanceof PersonRelationship) {
            throw new \LogicException('Invalid mapper usage.');
        }

        $dto = $target instanceof PersonRelationshipViewDto ? $target : new PersonRelationshipViewDto();

        $dto->id = $source->getPublicId();

        $dto->subjectId = $source->getSubject()->getPublicId();
        $dto->relatedPersonId = $source->getRelatedPerson()->getPublicId();

        $dto->type = $source->getType();
        $dto->isEmergencyContact = $source->isEmergencyContact();

        return $dto;
    }
}
