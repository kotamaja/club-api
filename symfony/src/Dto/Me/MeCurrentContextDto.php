<?php

namespace App\Dto\Me;

use App\Dto\Person\PersonListDto;

final readonly class MeCurrentContextDto
{
    public function __construct(
        public MeCurrentOrganizationDto $organization,
        public MeCurrentOrganizationUserDto $organizationUser,
        public ?PersonListDto $person,
    ) {
    }
}
