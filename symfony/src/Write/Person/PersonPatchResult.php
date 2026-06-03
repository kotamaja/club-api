<?php

namespace App\Write\Person;

use App\Entity\Person;

class PersonPatchResult
{
    public function __construct(private Person $person, private bool $changed)
    {
    }

    public function getPerson(): Person
    {
        return $this->person;
    }

    public function hasChanged(): bool
    {
        return $this->changed;
    }
}
