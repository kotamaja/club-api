<?php

namespace App\Write\ClubMembershipGroup;

use App\Entity\ClubMembershipGroup;

class ClubMembershipGroupPatchResult
{
    public function __construct(private ClubMembershipGroup $clubMembershipGroup, private bool $changed)
    {
    }

    public function getPersonContact(): ClubMembershipGroup
    {
        return $this->clubMembershipGroup;
    }

    public function hasChanged(): bool
    {
        return $this->changed;
    }
}
