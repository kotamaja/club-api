<?php

namespace App\Mapper\CustomMapper\Person;

use App\Dto\Person\PersonItemDto;
use App\Entity\Person;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;

#[Maps(source: Person::class, target: PersonItemDto::class)]
final class PersonToPersonItemDtoMapper implements CustomMapperInterface
{

    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof Person) {
            throw new \LogicException('Invalid mapper usage.');
        }

        $dto = $target instanceof PersonItemDto ? $target : new PersonItemDto();

        $dto->id = $source->getPublicId();
        $dto->firstname = $source->getFirstname();
        $dto->lastname = $source->getLastname();
        $dto->email = $source->getEmail();

        return $dto;
    }
}
