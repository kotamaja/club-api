<?php

namespace App\Write\Membership;

use App\Entity\Club;
use App\Entity\Membership;
use App\Entity\Person;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
 readonly class MembershipPeriodConflictChecker
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {
    }

    /**
     * Checks whether the given membership period overlaps with an existing membership
     * for the same person and club.
     *
     * The checked period is defined by a mandatory start date and an optional end date.
     * When the end date is null, the period is considered open-ended, meaning the
     * membership is currently active.
     *
     * The ignored membership parameter is useful when patching an existing membership.
     * Without it, the query would find the membership currently being updated and report
     * it as a conflict with itself.
     *
     * Overlap rule:
     * - an existing open-ended membership always overlaps if it starts before or during
     *   the checked period;
     * - an existing ended membership overlaps when its end date is greater than or equal
     *   to the checked start date;
     * - if the checked period has an end date, existing memberships must also start before
     *   or on that checked end date.
     *
     * @param Person $person The person for whom the period is checked.
     * @param Club $club The club for which the period is checked.
     * @param DateTimeImmutable $joinedAt The start date of the checked period.
     * @param DateTimeImmutable|null $endedAt The end date of the checked period, or null for an open-ended period.
     * @param Membership|null $ignoredMembership An existing membership to exclude from the conflict check.
     *
     * @return bool True when an overlapping membership exists, false otherwise.
     */
    public function hasOverlappingPeriod(Person             $person,
                                         Club               $club,
                                         DateTimeImmutable  $joinedAt,
                                         ?DateTimeImmutable $endedAt,
                                         ?Membership        $ignoredMembership = null,
    ): bool
    {
        $qb = $this->entityManager
            ->getRepository(Membership::class)
            ->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.person = :person')
            ->andWhere('m.club = :club')
            ->andWhere('m.endedAt IS NULL OR m.endedAt >= :joinedAt')
            ->setParameter('person', $person)
            ->setParameter('club', $club)
            ->setParameter('joinedAt', $joinedAt);

        if ($endedAt !== null) {
            $qb
                ->andWhere('m.joinedAt <= :endedAt')
                ->setParameter('endedAt', $endedAt);
        }

        if ($ignoredMembership !== null) {
            $qb
                ->andWhere('m != :ignoredMembership')
                ->setParameter('ignoredMembership', $ignoredMembership);
        }

        return (int)$qb->getQuery()->getSingleScalarResult() > 0;
    }
}
