<?php

namespace App\Core\Capability;

use App\Core\Enum\Feature;
use App\Core\Enum\Limit;
use App\Dto\Organization\OrganizationCapabilityItemDto;
use App\Entity\Organization;

final readonly class OrganizationCapabilityProvider
{
    public function __construct(private FeatureCheckerInterface $featureChecker, private LimitCheckerInterface $limitChecker)
    {
    }

    public function getCapabilities(Organization $organization): OrganizationCapabilityItemDto
    {
        $features = [];

        foreach (Feature::cases() as $feature) {
            $features[$feature->value] = $this->featureChecker->isEnabledForOrganization(
                $organization,
                $feature,
            );
        }

        $limits = [];

        foreach (Limit::cases() as $limit) {
            $limits[$limit->value] = $this->limitChecker->getLimitForOrganization(
                $organization,
                $limit,
            );
        }

        return new OrganizationCapabilityItemDto(
            servicePlan: $organization->getServicePlan()->value,
            features: $features,
            limits: $limits,
        );
    }
}
