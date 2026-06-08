<?php

namespace App\Write\ClubMembershipGroup;

use App\Dto\ClubMembershipGroup\ClubMembershipGroupCreateDto;
use App\Dto\ClubMembershipGroup\ClubMembershipGroupPatchDto;
use App\Entity\Club;
use App\Entity\ClubMembershipGroup;
use App\Entity\ConnectionUser;
use App\Security\OrganizationContext\CurrentOrganizationContext;
use App\Security\OrganizationContext\OrganizationScopeGuard;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ClubMembershipGroupWriteService implements ClubMembershipGroupWriteServiceInterface
{

    public function __construct(private CurrentOrganizationContext           $currentOrganizationContext,
                                private OrganizationScopeGuard               $organizationScopeGuard,
                                private ClubMembershipGroupPermissionChecker $permissionChecker,
                                private ClubMembershipGroupBusinessRules     $businessRules,
                                private EntityManagerInterface               $entityManager,
    )
    {
    }

    public function create(ClubMembershipGroupCreateDto $input, ConnectionUser $actor): ClubMembershipGroup
    {
        $organization = $this->currentOrganizationContext->getOrganization();

        $this->permissionChecker->assertCanCreate($actor, $organization);

        $club = $this->getClub($input->clubId);

        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($club);


        $clubMembershipGroup = ClubMembershipGroup::create(
            club: $club,
            name: $input->name,
            description: $input->description,
        );

        $this->entityManager->persist($clubMembershipGroup);

        return $clubMembershipGroup;
    }

    public function patch(ClubMembershipGroupPatchDto $input, ClubMembershipGroup $clubMembershipGroup, ConnectionUser $actor): ClubMembershipGroupPatchResult
    {
        $this->permissionChecker->assertCanUpdate($actor, $clubMembershipGroup);

        $this->organizationScopeGuard->assertBelongsToCurrentOrganization(
            $clubMembershipGroup->getClub()
        );

        $changed = false;

        if ($input->isNameProvided() && $input->getName() !== $clubMembershipGroup->getName()) {
            $clubMembershipGroup->changeName($input->getName());
            $changed = true;
        }

        if (
            $input->isDescriptionProvided()
            && $input->getDescription() !== $clubMembershipGroup->getDescription()
        ) {
            $clubMembershipGroup->changeDescription($input->getDescription());
            $changed = true;
        }


        return new ClubMembershipGroupPatchResult($clubMembershipGroup, $changed);
    }

    public function delete(ClubMembershipGroup $clubMembershipGroup, ConnectionUser $actor): void
    {
        $this->permissionChecker->assertCanDelete($actor, $clubMembershipGroup);

        $this->organizationScopeGuard->assertBelongsToCurrentOrganization(
            $clubMembershipGroup->getClub()
        );

        $this->businessRules->assertCanDelete($clubMembershipGroup);

        $this->entityManager->remove($clubMembershipGroup);
    }


    private function getClub(string $publicId): Club
    {

        $club = $this->entityManager->getRepository(Club::class)->findOneBy(['publicId' => $publicId,]);

        if (!$club instanceof Club) {
            throw new NotFoundHttpException('Club not found.');
        }

        return $club;
    }
}
