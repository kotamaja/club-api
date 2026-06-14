<?php

namespace App\Dto\Organization;

final readonly class OrganizationCapabilityItemDto
{
    /**
     * @param array<string, bool> $features
     * @param array<string, int|null> $limits
     */
    public function __construct(
        public string $servicePlan,
        public array $features,
        public array $limits,
    ) {
    }
}
