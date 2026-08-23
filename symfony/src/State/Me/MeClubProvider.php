<?php

namespace App\State\Me;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Me\MeClubDto;
use App\Entity\ConnectionUser;
use App\Entity\Club;
use App\Enum\UserStatus;
use App\Repository\ClubRepository;
use App\Repository\OrganizationRepository;
use App\Repository\OrganizationUserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @implements ProviderInterface<MeClubDto>
 */
final readonly class MeClubProvider implements ProviderInterface
{
    public function __construct(
        private Security $security,
        private OrganizationRepository $organizationRepository,
        private OrganizationUserRepository $organizationUserRepository,
        private ClubRepository $clubRepository,
    ) {
    }

    /**
     * @return list<MeClubDto>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $user = $this->security->getUser();

        if (!$user instanceof ConnectionUser) {
            throw new AccessDeniedHttpException('Authenticated ConnectionUser expected.');
        }

        if ($user->getStatus() !== UserStatus::Active) {
            throw new AccessDeniedHttpException('ConnectionUser is not active.');
        }

        $organizationId = $uriVariables['organizationId'] ?? null;

        if (!is_string($organizationId) || trim($organizationId) === '') {
            throw new AccessDeniedHttpException('Invalid organization.');
        }

        $organization = $this->organizationRepository->findOneByPublicId($organizationId);

        if ($organization === null || !$organization->isEnabled()) {
            throw new AccessDeniedHttpException('Invalid organization.');
        }

        $organizationUser = $this->organizationUserRepository
            ->findOneActiveByConnectionUserAndOrganization($user, $organization);

        if ($organizationUser === null) {
            throw new AccessDeniedHttpException('Invalid organization.');
        }

        $person = $organizationUser->getPerson();

        if ($person === null) {
            return [];
        }

        $clubs = $this->clubRepository->findAccessibleForPerson(
            organization: $organization,
            person: $person,
        );

        return array_map(
            static fn (Club $club): MeClubDto => new MeClubDto(
                clubId: $club->getPublicId(),
                clubName: $club->getName(),
            ),
            $clubs,
        );
    }
}
