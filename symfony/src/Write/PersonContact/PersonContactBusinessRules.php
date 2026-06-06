<?php

namespace App\Write\PersonContact;

use App\Entity\Person;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class PersonContactBusinessRules
{
    public function assertNotSelfContact(Person $person, Person $contactPerson): void
    {
        if ($person === $contactPerson || $person->getId() === $contactPerson->getId()) {
            throw new UnprocessableEntityHttpException('A person cannot be related to themselves.');
        }
    }
}
