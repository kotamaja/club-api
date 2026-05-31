<?php

namespace App\Write\Club;

use App\Entity\Club;

final readonly class ClubPatchResult
{
    public function __construct(private Club $club, private bool $changed)
    {
    }

    public function getClub(): Club
    {
        return $this->club;
    }

    public function hasChanged(): bool
    {
        return $this->changed;
    }
}
