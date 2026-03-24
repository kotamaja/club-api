<?php

namespace App\Dto\Membership;

use App\Dto\Club\ClubListDto;
use App\Dto\Person\PersonListDto;

final class MembershipItemDto
{
    public string $id;

    public PersonListDto $person;

    public ClubListDto $club;

    public \DateTimeImmutable $joinedAt;
    public ?\DateTimeImmutable $endedAt = null;

    public bool $isActive;
}
