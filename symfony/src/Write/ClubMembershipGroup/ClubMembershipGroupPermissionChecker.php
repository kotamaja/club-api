<?php

namespace App\Write\ClubMembershipGroup;

use App\Entity\ClubMembershipGroup;
use App\Entity\ConnectionUser;
use App\Entity\Organization;

class ClubMembershipGroupPermissionChecker
{
    public function assertCanCreate(ConnectionUser $actor, Organization $organization): void
    {
        // TODO: implement real permission check.
    }

    public function assertCanUpdate(ConnectionUser $actor, ClubMembershipGroup $clubMembershipGroup): void
    {
        // TODO: implement real permission check.
    }

    public function assertCanDelete(ConnectionUser $actor, ClubMembershipGroup $clubMembershipGroup): void
    {
        // TODO: implement real permission check.
    }
}
