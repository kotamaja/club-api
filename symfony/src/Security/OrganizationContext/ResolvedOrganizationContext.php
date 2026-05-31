<?php

namespace App\Security\OrganizationContext;

use App\Entity\Organization;
use App\Entity\OrganizationUser;

final readonly class ResolvedOrganizationContext
{
    public function __construct(public Organization $organization, public OrganizationUser $organizationUser)
    {
    }
}
