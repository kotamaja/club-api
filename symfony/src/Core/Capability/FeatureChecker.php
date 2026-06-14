<?php

namespace App\Core\Capability;

use App\Core\Enum\Feature;
use App\Core\Enum\ServicePlan;
use App\Core\Exception\FeatureNotAvailableException;
use App\Entity\Organization;

final readonly class FeatureChecker implements FeatureCheckerInterface
{
    public function __construct(private PlanCapabilityCatalog $catalog)
    {
    }

    public function isEnabled(ServicePlan $plan, Feature $feature): bool
    {
        return in_array($feature, $this->catalog->getFeatures($plan), true);
    }

    public function isEnabledForOrganization(Organization $organization, Feature $feature): bool
    {
        return $this->isEnabled($organization->getServicePlan(), $feature);
    }

    public function assertEnabled(ServicePlan $plan, Feature $feature): void
    {
        if (!$this->isEnabled($plan, $feature)) {
            throw new FeatureNotAvailableException($feature);
        }
    }

    public function assertEnabledForOrganization(Organization $organization, Feature $feature): void
    {
        $this->assertEnabled($organization->getServicePlan(), $feature);
    }
}
