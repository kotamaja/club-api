<?php

namespace App\Mapper\CustomMapper\Person;

use App\Dto\Person\PersonPatchDto;
use App\Entity\Person;
use App\Mapper\CustomMapperInterface;
use App\Mapper\InvalidInputException;
use App\Mapper\Maps;

#[Maps(source: PersonPatchDto::class, target: Person::class)]
final class PersonPatchDtoToPersonMapper implements CustomMapperInterface
{

    public function map(mixed $source, mixed $target = null): mixed
    {
        if (!$source instanceof PersonPatchDto) {
            throw new \LogicException('Invalid mapper usage.');
        }
        $person = $target instanceof Person ? $target : new Person();

        if ($source->isFirstnameProvided()) {
            $email = $source->getFirstname();
            if ($email === null || trim($email) === '') {
                throw new InvalidInputException('email cannot be blank', 'email');
            }
            $person->setFirstname($email);
        }

        if ($source->isLastnameProvided()) {
            $email = $source->getLastname();
            if ($email === null || trim($email) === '') {
                throw new InvalidInputException('email cannot be blank', 'email');
            }
            $person->setLastname($email);
        }

        if ($source->isEmailProvided()) {
            $email = $source->getEmail();
            if ($email === null || trim($email) === '') {
                throw new InvalidInputException('email cannot be blank', 'email');
            }
            $person->setEmail($email);
        }


        return $person;
    }
}
