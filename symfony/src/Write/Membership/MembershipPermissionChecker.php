<?php

namespace App\Write\Membership;

use App\Entity\ConnectionUser;
use App\Entity\Membership;
use App\Entity\Organization;

class MembershipPermissionChecker
{
    public function assertCanCreate(ConnectionUser $actor, Organization $organization): void
    {
        // TODO: implement real permission check.
    }

    public function assertCanUpdate(ConnectionUser $actor, Membership $membership): void
    {
        // TODO: implement real permission check.
    }

    public function assertCanDelete(ConnectionUser $actor, Membership $membership): void
    {
        // TODO: implement real permission check.
    }
}
