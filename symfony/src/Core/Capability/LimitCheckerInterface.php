<?php

namespace App\Core\Capability;

use App\Core\Enum\Limit;
use App\Core\Enum\ServicePlan;
use App\Entity\Organization;

interface LimitCheckerInterface
{
    public function getLimit(ServicePlan $plan, Limit $limit): ?int;

    public function getLimitForOrganization(Organization $organization, Limit $limit): ?int;

    public function assertWithinLimit(
        ServicePlan $plan,
        Limit $limit,
        int $currentValue,
        int $increment = 1,
    ): void;

    public function assertWithinLimitForOrganization(
        Organization $organization,
        Limit $limit,
        int $currentValue,
        int $increment = 1,
    ): void;
}
