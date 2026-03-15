<?php

namespace App\Mapper\CustomMapper\PersonContact;

use App\Dto\PersonContact\PersonContactCreateDto;
use App\Entity\PersonContact;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;

#[Maps(source: PersonContactCreateDto::class, target: PersonContact::class)]
final class PersonContactCreateDtoToPersonContactMapper implements CustomMapperInterface
{
    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof PersonContactCreateDto) {
            throw new \LogicException('Invalid mapper usage.');
        }
        $personRelationship = $target instanceof PersonContact ? $target : new PersonContact();

        $personRelationship->setType($source->type);
        $personRelationship->setIsEmergencyContact($source->isEmergencyContact);

        return $personRelationship;
    }
}
