<?php

namespace App\Tests\Api\Support;

use App\Entity\ConnectionUser;
use App\Entity\Organization;
use App\Entity\OrganizationUser;

final readonly class AuthenticatedOrganizationContext
{
    public function __construct(
        public ConnectionUser $connectionUser,
        public Organization $organization,
        public OrganizationUser $organizationUser,
        public string $jwt,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->jwt,
            'X-Organization-Id' => $this->organization->getPublicId(),
        ];
    }
}
