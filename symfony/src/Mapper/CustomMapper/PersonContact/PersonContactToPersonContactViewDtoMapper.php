<?php

namespace App\Mapper\CustomMapper\PersonContact;

use App\Dto\PersonContact\PersonContactViewDto;
use App\Entity\PersonContact;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;

#[Maps(source: PersonContact::class, target: PersonContactViewDto::class)]
final class PersonContactToPersonContactViewDtoMapper implements CustomMapperInterface
{
    public function map(mixed $source,  mixed $target = null): object
    {
        if (!$source instanceof PersonContact) {
            throw new \LogicException('Invalid mapper usage.');
        }

        $dto = $target instanceof PersonContactViewDto ? $target : new PersonContactViewDto();

        $dto->id = $source->getPublicId();

        $dto->personId = $source->getPerson()->getPublicId();
        $dto->contactPersonId = $source->getContactPerson()->getPublicId();

        $dto->type = $source->getType();
        $dto->isEmergencyContact = $source->isEmergencyContact();

        return $dto;
    }
}
