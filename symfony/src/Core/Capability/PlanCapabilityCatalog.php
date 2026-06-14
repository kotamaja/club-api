<?php

namespace App\Core\Capability;

use App\Core\Enum\Feature;
use App\Core\Enum\Limit;
use App\Core\Enum\ServicePlan;

final class PlanCapabilityCatalog
{
    /**
     * @return list<Feature>
     */
    public function getFeatures(ServicePlan $plan): array
    {
        return match ($plan) {
            ServicePlan::Community => [
                Feature::EventBasic,
                Feature::EventWaitlist,
            ],

            ServicePlan::Pro => [
                Feature::EventBasic,
                Feature::EventWaitlist,
                Feature::EventMultiSession,
                Feature::EventCustomForm,
                Feature::EventManualSelection,
                Feature::EventGroupVisibility,
                Feature::EventGroupEligibility,
                Feature::EventInterclub,
                Feature::EventAttendanceTracking,
                Feature::EventDocuments,
            ],
        };
    }

    /**
     * Returns limits for the given plan.
     *
     * A null value means unlimited.
     *
     * @return array<string, int|null>
     */
    public function getLimits(ServicePlan $plan): array
    {
        return match ($plan) {
            ServicePlan::Community => [
                Limit::MaxClubs->value => 3,
                Limit::MaxMembers->value => 250,
                Limit::MaxActiveEvents->value => 10,
                Limit::MaxEventParticipants->value => 50,
            ],

            ServicePlan::Pro => [
                Limit::MaxClubs->value => null,
                Limit::MaxMembers->value => null,
                Limit::MaxActiveEvents->value => null,
                Limit::MaxEventParticipants->value => null,
            ],
        };
    }
}
