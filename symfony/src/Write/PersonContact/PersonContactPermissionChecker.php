<?php

namespace App\Write\PersonContact;

use App\Entity\ConnectionUser;
use App\Entity\Organization;
use App\Entity\PersonContact;

class PersonContactPermissionChecker
{
    public function assertCanCreate(ConnectionUser $actor, Organization $organization): void
    {
        // TODO: implement real permission check.
    }

    public function assertCanUpdate(ConnectionUser $actor, PersonContact $personContact): void
    {
        // TODO: implement real permission check.
    }

    public function assertCanDelete(ConnectionUser $actor, PersonContact $personContact): void
    {
        // TODO: implement real permission check.
    }
}
