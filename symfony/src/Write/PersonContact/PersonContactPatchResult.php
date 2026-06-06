<?php

namespace App\Write\PersonContact;

use App\Entity\PersonContact;

class PersonContactPatchResult
{
    public function __construct(private PersonContact $personContact, private bool $changed)
    {
    }

    public function getPersonContact(): PersonContact
    {
        return $this->personContact;
    }

    public function hasChanged(): bool
    {
        return $this->changed;
    }
}
