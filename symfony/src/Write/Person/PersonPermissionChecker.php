<?php

namespace App\Write\Person;

use App\Entity\ConnectionUser;
use App\Entity\Organization;
use App\Entity\Person;

class PersonPermissionChecker
{
    public function assertCanCreate(ConnectionUser $actor, Organization $organization): void
    {
        // TODO: implement real permission check.
    }

    public function assertCanUpdate(ConnectionUser $actor, Person $person): void
    {
        // TODO: implement real permission check.
    }

    public function assertCanDelete(ConnectionUser $actor, Person $person): void
    {
        // TODO: implement real permission check.
    }
}
