<?php

namespace App\Write\Membership;

use App\Entity\Club;
use App\Entity\Membership;
use App\Entity\Person;
use App\Write\Exception\BusinessRuleViolationException;
use DateTimeImmutable;

 readonly class MembershipBusinessRules
{
    public function __construct(private MembershipPeriodConflictChecker $periodConflictChecker)
    {
    }

    /**
     * Ensures that the membership dates are chronologically valid.
     *
     * The start date is mandatory and the end date is optional.
     * When an end date is provided, it must not be earlier than the start date.
     *
     * @param DateTimeImmutable $joinedAt The date on which the membership starts.
     * @param DateTimeImmutable|null $endedAt The date on which the membership ends, or null for an active membership.
     *
     * @throws BusinessRuleViolationException When the end date is earlier than the start date.
     */
    public function assertDatesAreValid(DateTimeImmutable $joinedAt, ?DateTimeImmutable $endedAt): void
    {
        if ($endedAt !== null && $endedAt < $joinedAt) {
            throw new BusinessRuleViolationException(
                'Membership endedAt date cannot be earlier than joinedAt date.'
            );
        }
    }

    /**
     * Ensures that the given membership period does not overlap with another membership
     * for the same person and club.
     *
     * This rule protects the historical consistency of memberships. A person cannot have
     * two overlapping membership periods in the same club, whether the periods are active
     * or already ended.
     *
     * The ignored membership parameter is used during updates. It allows the checker to
     * exclude the membership currently being modified, otherwise the membership would
     * conflict with itself.
     *
     * @param Person $person The person concerned by the membership.
     * @param Club $club The club concerned by the membership.
     * @param DateTimeImmutable $joinedAt The start date of the period to validate.
     * @param DateTimeImmutable|null $endedAt The end date of the period to validate, or null for an active membership.
     * @param Membership|null $ignoredMembership The existing membership to ignore during conflict detection, usually the one being patched.
     *
     * @throws BusinessRuleViolationException When another membership period overlaps with the given period.
     */
    public function assertPeriodDoesNotOverlap(Person $person, Club $club, DateTimeImmutable $joinedAt, ?DateTimeImmutable $endedAt, ?Membership $ignoredMembership = null): void
    {
        if ($this->periodConflictChecker->hasOverlappingPeriod(
            person: $person,
            club: $club,
            joinedAt: $joinedAt,
            endedAt: $endedAt,
            ignoredMembership: $ignoredMembership,
        )) {
            throw new BusinessRuleViolationException(
                'This membership period overlaps with another membership for the same person and club.'
            );
        }
    }

    /**
     * Ensures that a membership can be physically deleted.
     *
     * A membership that already has an end date is considered historical data and must
     * not be deleted. Only an active membership, typically created by mistake, may be
     * removed.
     *
     * @param Membership $membership The membership to delete.
     *
     * @throws BusinessRuleViolationException When the membership has already ended.
     */
    public function assertCanDelete(Membership $membership): void
    {
        if ($membership->getEndedAt() !== null) {
            throw new BusinessRuleViolationException(
                'Cannot delete a membership that has already ended. Use it as historical data.'
            );
        }
    }
}
