<?php

namespace App\Core\Capability;

use App\Core\Enum\Feature;
use App\Core\Enum\ServicePlan;
use App\Entity\Organization;

interface FeatureCheckerInterface
{
    public function isEnabled(ServicePlan $plan, Feature $feature): bool;

    public function isEnabledForOrganization(Organization $organization, Feature $feature): bool;

    public function assertEnabled(ServicePlan $plan, Feature $feature): void;

    public function assertEnabledForOrganization(Organization $organization, Feature $feature): void;
}
