<?php

namespace App\ApiResource;


use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Dto\Person\PersonListDto;
use App\State\Me\MeOrganizationsProvider;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/me/organizations',
            provider: MeOrganizationsProvider::class,
        ),
    ],
)]
final readonly class MeOrganization
{
    public function __construct(
        public string $organizationUserId,
        public string $organizationId,
        public string $organizationName,
        public string $organizationSlug,
        public array $roles,
        public bool $enabled,
        public ?PersonListDto $person,
    ) {
    }
}
