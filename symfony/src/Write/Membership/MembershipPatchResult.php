<?php

namespace App\Write\Membership;

use App\Entity\Membership;

class MembershipPatchResult
{
    public function __construct(private Membership $membership, private bool $changed)
    {
    }

    public function getMembership(): Membership
    {
        return $this->membership;
    }

    public function hasChanged(): bool
    {
        return $this->changed;
    }
}
