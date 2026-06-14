<?php

namespace App\Core\Capability;

use App\Core\Enum\Limit;
use App\Core\Enum\ServicePlan;
use App\Core\Exception\LimitExceededException;
use App\Entity\Organization;

final readonly class LimitChecker implements LimitCheckerInterface
{
    public function __construct(private PlanCapabilityCatalog $catalog)
    {
    }

    public function getLimit(ServicePlan $plan, Limit $limit): ?int
    {
        $limits = $this->catalog->getLimits($plan);

        return $limits[$limit->value] ?? null;
    }

    public function getLimitForOrganization(Organization $organization, Limit $limit): ?int
    {
        return $this->getLimit($organization->getServicePlan(), $limit);
    }

    public function assertWithinLimit(
        ServicePlan $plan,
        Limit       $limit,
        int         $currentValue,
        int         $increment = 1,
    ): void
    {
        $max = $this->getLimit($plan, $limit);

        if ($max === null) {
            return;
        }

        if ($currentValue + $increment > $max) {
            throw new LimitExceededException(
                limit: $limit,
                max: $max,
                currentValue: $currentValue,
                increment: $increment,
            );
        }
    }

    public function assertWithinLimitForOrganization(
        Organization $organization,
        Limit        $limit,
        int          $currentValue,
        int          $increment = 1,
    ): void
    {
        $this->assertWithinLimit(
            $organization->getServicePlan(),
            $limit,
            $currentValue,
            $increment,
        );
    }
}
