<?php

namespace App\State\Me;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\MeOrganization;
use App\Dto\Person\PersonListDto;
use App\Entity\ConnectionUser;
use App\Entity\OrganizationUser;
use App\Repository\OrganizationUserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @implements ProviderInterface<MeOrganization>
 */
final readonly class MeOrganizationsProvider implements ProviderInterface
{
    public function __construct(
        private Security                   $security,
        private OrganizationUserRepository $organizationUserRepository,
    )
    {
    }

    /**
     * @return list<MeOrganization>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $user = $this->security->getUser();

        if (!$user instanceof ConnectionUser) {
            throw new AccessDeniedHttpException('Authenticated ConnectionUser expected.');
        }

        $organizationUsers = $this->organizationUserRepository->findActiveByConnectionUser($user);

        return array_map(
            static function (OrganizationUser $organizationUser): MeOrganization {
                $organization = $organizationUser->getOrganization();
                $person = $organizationUser->getPerson();


                $personDto = new PersonListDto();
                $personDto->id = $person->getPublicId();
                $personDto->firstname = $person->getFirstname();
                $personDto->lastname = $person->getLastname();
                $personDto->email = $person->getEmail();

                return new MeOrganization(
                    organizationUserId: $organizationUser->getPublicId(),
                    organizationId: $organization->getPublicId(),
                    organizationName: $organization->getName(),
                    organizationSlug: $organization->getSlug(),
                    roles: $organizationUser->getRoles(),
                    enabled: $organizationUser->isEnabled(),
                    person: $personDto
                );
            },
            $organizationUsers,
        );
    }
}
