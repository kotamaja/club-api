<?php

namespace App\Write\Person;

use App\Dto\Person\PersonCreateDto;
use App\Dto\Person\PersonPatchDto;
use App\Entity\ConnectionUser;
use App\Entity\Person;

interface PersonWriteServiceInterface
{
    public function create(PersonCreateDto $input, ConnectionUser $actor): Person;

    public function patch(PersonPatchDto $input, Person $person, ConnectionUser $actor): PersonPatchResult;

    public function delete(Person $person, ConnectionUser $actor): void;
}
