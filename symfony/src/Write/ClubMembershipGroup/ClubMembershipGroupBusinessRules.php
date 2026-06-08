<?php

namespace App\Write\ClubMembershipGroup;

use App\Entity\ClubMembershipGroup;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ClubMembershipGroupBusinessRules
{

    public function assertCanDelete(ClubMembershipGroup $group): void
    {
        if (!$group->getClubMembershipGroupMemberships()->isEmpty()) {
            throw new UnprocessableEntityHttpException(
                'Cannot delete a group that still contains memberships.'
            );
        }
    }

}
