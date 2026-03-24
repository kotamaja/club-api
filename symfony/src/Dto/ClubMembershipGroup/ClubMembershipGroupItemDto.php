<?php

namespace App\Dto\ClubMembershipGroup;

use App\Dto\Club\ClubListDto;
use App\Dto\Person\PersonListDto;

class ClubMembershipGroupItemDto
{
    public string $id;

    public ClubListDto $club;

    public string $name;

    public string $description;

    public int $membershipCount;
}
