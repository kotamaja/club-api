<?php

namespace App\Dto\ClubMembershipGroupMembership;

class ClubMembershipGroupMembershipListDto
{
    public string $id;
    public string $clubMembershipGroupId;
    public string $membershipId;
    public ?string $notes = null;

    public ?string $clubMembershipGroupName = null;
    public ?string $personFirstname = null;
    public ?string $personLastname = null;
}
