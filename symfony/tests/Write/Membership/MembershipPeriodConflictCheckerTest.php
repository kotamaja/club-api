<?php

namespace App\Tests\Write\Membership;

use App\Factory\ClubFactory;
use App\Factory\MembershipFactory;
use App\Factory\OrganizationFactory;
use App\Factory\PersonFactory;
use App\Write\Membership\MembershipPeriodConflictChecker;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

final class MembershipPeriodConflictCheckerTest extends KernelTestCase
{
    use ResetDatabase;

    private MembershipPeriodConflictChecker $checker;

    protected function setUp(): void
    {
        parent::setUp();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $this->checker = new MembershipPeriodConflictChecker($entityManager);
    }

    public function testDetectsConflictWithExistingActiveMembership(): void
    {

        $organization = OrganizationFactory::new()
            ->withNameAndSlug('Test Organization', 'test-organization')
            ->create();

        $club = ClubFactory::new()->forOrganization($organization)->create();
        $person = PersonFactory::new()->forOrganization($organization)->create();

        MembershipFactory::createOne([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new DateTimeImmutable('2024-01-01'),
            'endedAt' => null,
        ]);

        $hasConflict = $this->checker->hasOverlappingPeriod(
            person: $person,
            club: $club,
            joinedAt: new DateTimeImmutable('2025-01-01'),
            endedAt: null,
        );

        self::assertTrue($hasConflict);
    }

    public function testDetectsConflictWithHistoricalOverlappingMembership(): void
    {
        $organization = OrganizationFactory::new()
            ->withNameAndSlug('Test Organization', 'test-organization')
            ->create();

        $club = ClubFactory::new()->forOrganization($organization)->create();
        $person = PersonFactory::new()->forOrganization($organization)->create();

        MembershipFactory::createOne([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new DateTimeImmutable('2024-01-01'),
            'endedAt' => new DateTimeImmutable('2024-12-31'),
        ]);

        $hasConflict = $this->checker->hasOverlappingPeriod(
            person: $person,
            club: $club,
            joinedAt: new DateTimeImmutable('2024-06-01'),
            endedAt: new DateTimeImmutable('2024-08-31'),
        );

        self::assertTrue($hasConflict);
    }

    public function testAllowsPeriodBeforeExistingMembershipWithoutOverlap(): void
    {
        $organization = OrganizationFactory::new()
            ->withNameAndSlug('Test Organization', 'test-organization')
            ->create();

        $club = ClubFactory::new()->forOrganization($organization)->create();
        $person = PersonFactory::new()->forOrganization($organization)->create();

        MembershipFactory::createOne([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new DateTimeImmutable('2024-06-01'),
            'endedAt' => new DateTimeImmutable('2024-12-31'),
        ]);

        $hasConflict = $this->checker->hasOverlappingPeriod(
            person: $person,
            club: $club,
            joinedAt: new DateTimeImmutable('2024-01-01'),
            endedAt: new DateTimeImmutable('2024-05-31'),
        );

        self::assertFalse($hasConflict);
    }

    public function testAllowsPeriodAfterExistingMembershipWithoutOverlap(): void
    {
        $organization = OrganizationFactory::new()
            ->withNameAndSlug('Test Organization', 'test-organization')
            ->create();

        $club = ClubFactory::new()->forOrganization($organization)->create();
        $person = PersonFactory::new()->forOrganization($organization)->create();

        MembershipFactory::createOne([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new DateTimeImmutable('2024-01-01'),
            'endedAt' => new DateTimeImmutable('2024-05-31'),
        ]);

        $hasConflict = $this->checker->hasOverlappingPeriod(
            person: $person,
            club: $club,
            joinedAt: new DateTimeImmutable('2024-06-01'),
            endedAt: null,
        );

        self::assertFalse($hasConflict);
    }

    public function testIgnoresCurrentMembershipDuringPatch(): void
    {
        $organization = OrganizationFactory::new()
            ->withNameAndSlug('Test Organization', 'test-organization')
            ->create();

        $club = ClubFactory::new()->forOrganization($organization)->create();
        $person = PersonFactory::new()->forOrganization($organization)->create();

        $membership = MembershipFactory::createOne([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new DateTimeImmutable('2024-01-01'),
            'endedAt' => null,
        ]);

        $hasConflict = $this->checker->hasOverlappingPeriod(
            person: $person,
            club: $club,
            joinedAt: new DateTimeImmutable('2024-01-01'),
            endedAt: null,
            ignoredMembership: $membership,
        );

        self::assertFalse($hasConflict);
    }

    public function testStillDetectsAnotherMembershipWhenCurrentMembershipIsIgnored(): void
    {
        $organization = OrganizationFactory::new()
            ->withNameAndSlug('Test Organization', 'test-organization')
            ->create();

        $club = ClubFactory::new()->forOrganization($organization)->create();
        $person = PersonFactory::new()->forOrganization($organization)->create();

        $currentMembership = MembershipFactory::createOne([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new DateTimeImmutable('2023-01-01'),
            'endedAt' => new DateTimeImmutable('2023-12-31'),
        ]);

        MembershipFactory::createOne([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new DateTimeImmutable('2024-01-01'),
            'endedAt' => null,
        ]);

        $hasConflict = $this->checker->hasOverlappingPeriod(
            person: $person,
            club: $club,
            joinedAt: new DateTimeImmutable('2024-06-01'),
            endedAt: null,
            ignoredMembership: $currentMembership,
        );

        self::assertTrue($hasConflict);
    }
}
