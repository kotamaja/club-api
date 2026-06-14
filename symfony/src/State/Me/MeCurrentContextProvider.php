<?php

namespace App\State\Me;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Core\Capability\OrganizationCapabilityProvider;
use App\Dto\Me\MeCurrentContextDto;
use App\Dto\Me\MeCurrentOrganizationDto;
use App\Dto\Me\MeCurrentOrganizationUserDto;
use App\Dto\Me\MePersonSummaryDto;
use App\Security\OrganizationContext\CurrentOrganizationContext;

class MeCurrentContextProvider implements ProviderInterface
{
    public function __construct(private CurrentOrganizationContext $currentOrganizationContext,
                                private readonly OrganizationCapabilityProvider $organizationCapabilityProvider,
    )
    {
    }


    public function provide(Operation $operation, array $uriVariables = [], array $context = []): MeCurrentContextDto
    {

        $organization = $this->currentOrganizationContext->getOrganization();
        $organizationUser = $this->currentOrganizationContext->getOrganizationUser();
        $person = $this->currentOrganizationContext->getPerson();

        return new MeCurrentContextDto(
            organization: new MeCurrentOrganizationDto(
                id: $organization->getPublicId(),
                name: $organization->getName(),
                slug: $organization->getSlug(),
            ),
            organizationUser: new MeCurrentOrganizationUserDto(
                id: $organizationUser->getPublicId(),
                roles: $organizationUser->getRoles(),
                enabled: $organizationUser->isEnabled(),
            ),
            person: $person === null
                ? null
                : new MePersonSummaryDto(
                    id: $person->getPublicId(),
                    firstName: $person->getFirstName(),
                    lastName: $person->getLastName(),
                ),
            capabilities: $this->organizationCapabilityProvider->getCapabilities($organization),
        );

    }
}
