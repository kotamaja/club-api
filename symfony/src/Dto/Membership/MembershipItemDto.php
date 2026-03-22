<?php

namespace App\Dto\Membership;

final class MembershipItemDto
{
    public string $id;
    public string $personId;
    public string $clubId;

    public \DateTimeImmutable $joinedAt;
    public ?\DateTimeImmutable $endedAt = null;

    public bool $isActive;
}
