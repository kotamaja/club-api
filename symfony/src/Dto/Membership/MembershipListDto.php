<?php

namespace App\Dto\Membership;

final class MembershipListDto
{
    public string $id;
    public string $personId;
    public string $personFirstName;
    public string $personLastName;

    public string $clubId;
    public string $clubName;

    public \DateTimeImmutable $joinedAt;
    public ?\DateTimeImmutable $endedAt = null;

    public bool $isActive;
}
