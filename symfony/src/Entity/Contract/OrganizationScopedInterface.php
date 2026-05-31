<?php

namespace App\Entity\Contract;

use App\Entity\Organization;

interface OrganizationScopedInterface
{
    public function getOrganization(): Organization;
}
