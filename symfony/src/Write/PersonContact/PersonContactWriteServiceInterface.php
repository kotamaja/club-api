<?php

namespace App\Write\PersonContact;

use App\Dto\PersonContact\PersonContactCreateDto;
use App\Dto\PersonContact\PersonContactPatchDto;
use App\Entity\ConnectionUser;
use App\Entity\PersonContact;

interface PersonContactWriteServiceInterface
{
    public function create(PersonContactCreateDto $input, ConnectionUser $actor): PersonContact;

    public function patch(PersonContactPatchDto $input, PersonContact $personContact, ConnectionUser $actor): PersonContactPatchResult;

    public function delete(PersonContact $personContact, ConnectionUser $actor): void;
}
