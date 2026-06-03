<?php

namespace App\Write\Membership;

use App\Dto\Membership\MembershipCreateDto;
use App\Dto\Membership\MembershipPatchDto;
use App\Dto\Utils\DateUtils;
use App\Entity\Club;
use App\Entity\ConnectionUser;
use App\Entity\Membership;
use App\Entity\Person;
use App\Security\OrganizationContext\CurrentOrganizationContext;
use App\Security\OrganizationContext\OrganizationScopeGuard;
use App\Write\Exception\BusinessRuleViolationException;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MembershipWriteService implements MembershipWriteServiceInterface
{

    public function __construct(private CurrentOrganizationContext  $currentOrganizationContext,
                                private OrganizationScopeGuard      $organizationScopeGuard,
                                private MembershipPermissionChecker $membershipPermissionChecker,
                                private MembershipBusinessRules     $membershipBusinessRules,
                                private EntityManagerInterface      $entityManager,
    )
    {
    }


    public function create(MembershipCreateDto $input, ConnectionUser $actor): Membership
    {
        $organization = $this->currentOrganizationContext->getOrganization();

        $this->membershipPermissionChecker->assertCanCreate($actor, $organization);

        $person = $this->getPerson($input->personId);
        $club = $this->getClub($input->clubId);

        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($person);
        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($club);

        $joinedAt = new DateTimeImmutable();
        $endedAt = null;

        $this->membershipBusinessRules->assertPeriodDoesNotOverlap(
            person: $person,
            club: $club,
            joinedAt: $joinedAt,
            endedAt: $endedAt,
        );

        $membership = Membership::create(person: $person, club: $club, joinedAt: $joinedAt);

        $this->applyCreateData($input, $membership);

        $this->entityManager->persist($membership);

        return $membership;
    }

    public function patch(MembershipPatchDto $input, Membership $membership, ConnectionUser $actor): MembershipPatchResult
    {

        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($membership);

        $this->membershipPermissionChecker->assertCanUpdate($actor, $membership);

        $changed = $this->applyPatchData($input, $membership) ;

        if ($changed) {
            $this->membershipBusinessRules->assertDatesAreValid(
                joinedAt: $membership->getJoinedAt(),
                endedAt: $membership->getEndedAt(),
            );

            $this->membershipBusinessRules->assertPeriodDoesNotOverlap(
                person: $membership->getPerson(),
                club: $membership->getClub(),
                joinedAt: $membership->getJoinedAt(),
                endedAt: $membership->getEndedAt(),
                ignoredMembership: $membership,
            );
        }

        return new MembershipPatchResult($membership, $changed);
    }

    public function delete(Membership $membership, ConnectionUser $actor): void
    {
        $this->organizationScopeGuard->assertBelongsToCurrentOrganization($membership);

        $this->membershipPermissionChecker->assertCanDelete($actor, $membership);

        $this->membershipBusinessRules->assertCanDelete($membership);

        $this->entityManager->remove($membership);
    }


    private function getPerson(string $publicId): Person
    {

        $person = $this->entityManager->getRepository(Person::class)->findOneBy([
            'publicId' => $publicId,
        ]);

        if (!$person instanceof Person) {
            throw new NotFoundHttpException('Person not found.');
        }

        return $person;
    }

    private function getClub(string $publicId): Club
    {

        $club = $this->entityManager->getRepository(Club::class)->findOneBy([
            'publicId' => $publicId,
        ]);

        if (!$club instanceof Club) {
            throw new NotFoundHttpException('Club not found.');
        }

        return $club;
    }


    private function applyCreateData(MembershipCreateDto $input, Membership $membership): void
    {
        // nothing yet
    }

    private function applyPatchData(MembershipPatchDto $input, Membership $membership): bool
    {
        $changed = false;

        $changed = $this->applyJoinedAtDate($input, $membership) || $changed;
        $changed = $this->applyEndedAtDate($input, $membership) || $changed;

        return $changed;
    }


    private function applyJoinedAtDate(MembershipPatchDto $input, Membership $membership): bool
    {
        if (!$input->isJoinedAtProvided()) {
            return false;
        }

        $newDate = DateUtils::fromString($input->getJoinedAt(), 'Y-m-d');

        if (!$newDate instanceof DateTimeImmutable) {
            throw new BusinessRuleViolationException('Membership joinedAt date is required.');
        }

        $current = $membership->getJoinedAt();

        if ($newDate->format('Y-m-d') === $current?->format('Y-m-d')) {
            return false;
        }

        $membership->setJoinedAt($newDate);

        return true;
    }

    private function applyEndedAtDate(MembershipPatchDto $input, Membership $membership): bool
    {
        if (!$input->isEndedAtProvided()) {
            return false;
        }

        $newDate = DateUtils::fromString($input->getEndedAt(), 'Y-m-d');
        $current = $membership->getEndedAt();

        if ($newDate?->format('Y-m-d') === $current?->format('Y-m-d')) {
            return false;
        }

        $membership->setEndedAt($newDate);

        return true;
    }


}
