<?php

namespace App\Write\Club;

use App\Entity\Club;
use App\Entity\ConnectionUser;
use App\Entity\Organization;

final readonly class ClubPermissionChecker
{
    public function assertCanCreate(ConnectionUser $actor, Organization $organization): void
    {
        // TODO: implement real permission check.
    }

    public function assertCanUpdate(ConnectionUser $actor, Club $club): void
    {
        // TODO: implement real permission check.
    }

    public function assertCanDelete(ConnectionUser $actor, Club $club): void
    {
        // TODO: implement real permission check.
    }
}
