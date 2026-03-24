<?php

namespace App\Dto\ClubMembershipGroupMembership;

use Symfony\Component\Validator\Constraints as Assert;

class ClubMembershipGroupMembershipCreateDto
{

    #[Assert\NotBlank]
    #[Assert\Ulid]
    public string $clubMembershipGroupId;

    #[Assert\NotBlank]
    #[Assert\Ulid]
    public string $membershipId;
    public ?string $notes = null;

}
