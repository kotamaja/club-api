<?php

namespace App\Dto\Me;

use App\Dto\Organization\OrganizationCapabilityItemDto;
use App\Dto\Person\PersonItemDto;
use App\Dto\Person\PersonListDto;

final readonly class MeCurrentContextDto
{
    public function __construct(
        public MeCurrentOrganizationDto $organization,
        public MeCurrentOrganizationUserDto $organizationUser,
        public ?MePersonSummaryDto $person,
        public OrganizationCapabilityItemDto $capabilities,
    ) {
    }
}
