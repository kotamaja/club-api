<?php

namespace App\Dto\ClubMembershipGroup;


use Symfony\Component\Validator\Constraints as Assert;
class ClubMembershipGroupCreateDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 512)]
    public ?string $name = null;

    #[Assert\Length(max: 2000)]
    public ?string $description = null;

    #[Assert\NotBlank]
    #[Assert\Ulid]
    public string $clubId;


}
