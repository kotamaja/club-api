<?php

namespace App\Mapper\CustomMapper\Person;

use App\Dto\Person\PersonCreateDto;
use App\Entity\Person;
use App\Mapper\CustomMapperInterface;
use App\Mapper\InvalidInputException;
use App\Mapper\Maps;


#[Maps(source: PersonCreateDto::class, target: Person::class)]
final class PersonCreateDtoToPersonMapper implements CustomMapperInterface
{

    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof PersonCreateDto) {
            throw new \LogicException('Invalid mapper usage.');
        }
        $person = $target instanceof Person ? $target : new Person();

        if (trim($source->firstname) === '') {
            throw new InvalidInputException('firstname cannot be blank', 'firstname');
        }
        $person->setFirstname($source->firstname);

        if (trim($source->lastname) === '') {
            throw new InvalidInputException('lastname cannot be blank', 'lastname');
        }
        $person->setLastname($source->lastname);

        if (trim($source->email) === '') {
            throw new InvalidInputException('email cannot be blank', 'email');
        }
        $person->setEmail($source->email);

        return $person;

    }
}
