<?php

namespace App\Dto\ClubMembershipGroupMembership;

use App\Dto\ClubMembershipGroup\ClubMembershipGroupListDto;
use App\Dto\Membership\MembershipListDto;

class ClubMembershipGroupMembershipItemDto
{
    public string $id;
    public ClubMembershipGroupListDto $clubMembershipGroup;

    public MembershipListDto $membership;

    public ?string $notes;
}




