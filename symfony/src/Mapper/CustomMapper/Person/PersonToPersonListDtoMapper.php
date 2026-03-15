<?php

namespace App\Mapper\CustomMapper\Person;

use App\Dto\Person\PersonListDto;
use App\Entity\Person;
use App\Mapper\CustomMapperInterface;
use App\Mapper\Maps;

#[Maps(source: Person::class, target: PersonListDto::class)]
final class PersonToPersonListDtoMapper implements CustomMapperInterface
{

    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof Person) {
            throw new \LogicException('Invalid mapper usage.');
        }

        $dto = $target instanceof PersonListDto ? $target : new PersonListDto();

        $dto->id = $source->getPublicId();
        $dto->firstname = $source->getFirstname();
        $dto->lastname = $source->getLastname();
        $dto->email = $source->getEmail();

        return $dto;
    }
}
