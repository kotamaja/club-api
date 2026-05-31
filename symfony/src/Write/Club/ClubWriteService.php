<?php

namespace App\Write\Club;

use App\Dto\Club\ClubCreateDto;
use App\Dto\Club\ClubPatchDto;
use App\Entity\Club;
use App\Entity\ConnectionUser;
use App\Repository\MembershipRepository;
use App\Security\OrganizationContext\CurrentOrganizationContext;
use App\Security\OrganizationContext\OrganizationScopeGuard;
use App\Write\Exception\BusinessRuleViolationException;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ClubWriteService implements ClubWriteServiceInterface
{

    public function __construct(private CurrentOrganizationContext $currentOrganizationContext,
                                private OrganizationScopeGuard     $organizationScopeGuard,
                                private ClubPermissionChecker      $clubPermissionChecker,
                                private EntityManagerInterface     $entityManager,
                                private MembershipRepository       $membershipRepository,
    )
    {
    }

    public function create(ClubCreateDto $input, ConnectionUser $actor): Club
    {
        $organization = $this->currentOrganizationContext->getOrganization();

        $this->clubPermissionChecker->assertCanCreate($actor, $organization);

        $club = Club::create(organization: $organization, name: $input->name);

        $this->applyCreateData($input, $club);

        $this->entityManager->persist($club);

        return $club;
    }

    public function patch(ClubPatchDto $input, Club $club, ConnectionUser $actor): ClubPatchResult
    {

        $changed = false;

        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($club);

        $this->clubPermissionChecker->assertCanUpdate($actor, $club);

        $changed = $this->applyPatchData($input, $club) || $changed;


        return new ClubPatchResult($club, $changed);
    }

    public function delete(Club $club, ConnectionUser $actor): void
    {
        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($club);

        $this->clubPermissionChecker->assertCanDelete($actor, $club);

        if (0 < $this->membershipRepository->count(['club' => $club])) {
            throw new BusinessRuleViolationException(
                'This club cannot be deleted because it is still used by one or more membership.'
            );
        }


        $this->entityManager->remove($club);

    }

    private function applyCreateData(ClubCreateDto $input, Club $club): void
    {
        // nothing yet
    }

    private function applyPatchData(ClubPatchDto $input, Club $club): bool
    {
        $changed = false;

        $changed = $this->applyName($input, $club) || $changed;

        return $changed;
    }

    private function applyName(ClubPatchDto $input, Club $club): bool
    {
        if (!$input->isNameProvided()) {
            return false;
        }

        $name = $input->getName();

        if ($name === $club->getName()) {
            return false;
        }

        $club->rename($name);

        return true;
    }

}
