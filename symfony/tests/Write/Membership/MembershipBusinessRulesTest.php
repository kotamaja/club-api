<?php

namespace App\Tests\Write\Membership;

use App\Entity\Club;
use App\Entity\Membership;
use App\Entity\Person;
use App\Write\Exception\BusinessRuleViolationException;
use App\Write\Membership\MembershipBusinessRules;
use App\Write\Membership\MembershipPeriodConflictChecker;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class MembershipBusinessRulesTest extends TestCase
{
    public function testDatesAreValidWhenEndedAtIsNull(): void
    {
        $rules = $this->createRules();

        $rules->assertDatesAreValid(
            joinedAt: new DateTimeImmutable('2024-01-01'),
            endedAt: null,
        );

        self::addToAssertionCount(1);
    }

    public function testDatesAreValidWhenEndedAtIsAfterJoinedAt(): void
    {
        $rules = $this->createRules();

        $rules->assertDatesAreValid(
            joinedAt: new DateTimeImmutable('2024-01-01'),
            endedAt: new DateTimeImmutable('2024-12-31'),
        );

        self::addToAssertionCount(1);
    }

    public function testDatesAreValidWhenEndedAtIsSameAsJoinedAt(): void
    {
        $rules = $this->createRules();

        $rules->assertDatesAreValid(
            joinedAt: new DateTimeImmutable('2024-01-01'),
            endedAt: new DateTimeImmutable('2024-01-01'),
        );

        self::addToAssertionCount(1);
    }

    public function testDatesAreInvalidWhenEndedAtIsBeforeJoinedAt(): void
    {
        $rules = $this->createRules();

        $this->expectException(BusinessRuleViolationException::class);
        $this->expectExceptionMessage('Membership endedAt date cannot be earlier than joinedAt date.');

        $rules->assertDatesAreValid(
            joinedAt: new DateTimeImmutable('2024-06-01'),
            endedAt: new DateTimeImmutable('2024-01-01'),
        );
    }

    public function testPeriodDoesNotOverlapWhenCheckerReturnsFalse(): void
    {
        $person = $this->createStub(Person::class);
        $club = $this->createStub(Club::class);
        $joinedAt = new DateTimeImmutable('2024-01-01');
        $endedAt = new DateTimeImmutable('2024-12-31');

        $checker = $this->createMock(MembershipPeriodConflictChecker::class);
        $checker
            ->expects(self::once())
            ->method('hasOverlappingPeriod')
            ->with(
                self::identicalTo($person),
                self::identicalTo($club),
                self::equalTo($joinedAt),
                self::equalTo($endedAt),
                self::isNull(),
            )
            ->willReturn(false);

        $rules = new MembershipBusinessRules($checker);

        $rules->assertPeriodDoesNotOverlap(
            person: $person,
            club: $club,
            joinedAt: $joinedAt,
            endedAt: $endedAt,
        );
    }

    public function testPeriodDoesNotOverlapThrowsWhenCheckerReturnsTrue(): void
    {
        $person = $this->createStub(Person::class);
        $club = $this->createStub(Club::class);
        $joinedAt = new DateTimeImmutable('2024-01-01');
        $endedAt = null;

        $checker = $this->createMock(MembershipPeriodConflictChecker::class);
        $checker
            ->expects(self::once())
            ->method('hasOverlappingPeriod')
            ->with(
                self::identicalTo($person),
                self::identicalTo($club),
                self::equalTo($joinedAt),
                self::isNull(),
                self::isNull(),
            )
            ->willReturn(true);

        $rules = new MembershipBusinessRules($checker);

        $this->expectException(BusinessRuleViolationException::class);
        $this->expectExceptionMessage('This membership period overlaps with another membership for the same person and club.');

        $rules->assertPeriodDoesNotOverlap(
            person: $person,
            club: $club,
            joinedAt: $joinedAt,
            endedAt: $endedAt,
        );
    }

    public function testPeriodDoesNotOverlapIgnoresGivenMembership(): void
    {
        $person = $this->createStub(Person::class);
        $club = $this->createStub(Club::class);
        $ignoredMembership = $this->createStub(Membership::class);
        $joinedAt = new DateTimeImmutable('2024-01-01');
        $endedAt = null;

        $checker = $this->createMock(MembershipPeriodConflictChecker::class);
        $checker
            ->expects(self::once())
            ->method('hasOverlappingPeriod')
            ->with(
                self::identicalTo($person),
                self::identicalTo($club),
                self::equalTo($joinedAt),
                self::isNull(),
                self::identicalTo($ignoredMembership),
            )
            ->willReturn(false);

        $rules = new MembershipBusinessRules($checker);

        $rules->assertPeriodDoesNotOverlap(
            person: $person,
            club: $club,
            joinedAt: $joinedAt,
            endedAt: $endedAt,
            ignoredMembership: $ignoredMembership,
        );
    }

    public function testCanDeleteActiveMembership(): void
    {
        $membership = $this->createMock(Membership::class);
        $membership
            ->expects(self::once())
            ->method('getEndedAt')
            ->willReturn(null);

        $rules = $this->createRules();

        $rules->assertCanDelete($membership);
    }

    public function testCannotDeleteEndedMembership(): void
    {
        $membership = $this->createMock(Membership::class);
        $membership
            ->expects(self::once())
            ->method('getEndedAt')
            ->willReturn(new DateTimeImmutable('2024-12-31'));

        $rules = $this->createRules();

        $this->expectException(BusinessRuleViolationException::class);
        $this->expectExceptionMessage('Cannot delete a membership that has already ended. Use it as historical data.');

        $rules->assertCanDelete($membership);
    }

    private function createRules(): MembershipBusinessRules
    {
        return new MembershipBusinessRules(
            $this->createStub(MembershipPeriodConflictChecker::class),
        );
    }
}
