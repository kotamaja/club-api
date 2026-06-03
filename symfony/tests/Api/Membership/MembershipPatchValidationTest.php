<?php

namespace App\Tests\Api\Membership;

use App\Factory\ClubFactory;
use App\Factory\PersonFactory;
use App\Tests\ApiTestCase;
use App\Factory\MembershipFactory;

final class MembershipPatchValidationTest extends ApiTestCase
{
    public function testPatchRejectsEndedAtBeforeJoinedAt(): void
    {

        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $club = ClubFactory::new()->forOrganization($organization)->create();

        $membership = MembershipFactory::new()
            ->forClub($club)
            ->forPerson($person)
            ->create([
                'joinedAt' => new \DateTimeImmutable('2024-06-01T00:00:00+00:00'),
                'endedAt' => null,
            ]);



        $this->apiPatch('/api/v1/memberships/'.$membership->getPublicId(), [
            'endedAt' => '2024-01-01T00:00:00+00:00',
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testPatchRejectsNullJoinedAt(): void
    {

        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $club = ClubFactory::new()->forOrganization($organization)->create();

        $membership = MembershipFactory::new()
            ->forClub($club)
            ->forPerson($person)
            ->create([
                'joinedAt' => new \DateTimeImmutable('2024-01-01T00:00:00+00:00'),
                'endedAt' => null,
            ]);

        $this->apiPatch('/api/v1/memberships/'.$membership->getPublicId(), [
            'joinedAt' => null,
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testPatchRejectsInvalidJoinedAtFormat(): void
    {

        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $club = ClubFactory::new()->forOrganization($organization)->create();

        $membership = MembershipFactory::new()
            ->forClub($club)
            ->forPerson($person)
            ->create([
                'joinedAt' => new \DateTimeImmutable('2024-01-01T00:00:00+00:00'),
                'endedAt' => null,
            ]);



        $this->apiPatch('/api/v1/memberships/'.$membership->getPublicId(), [
            'joinedAt' => 'not-a-date',
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testPatchRejectsInvalidEndedAtFormat(): void
    {

        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $club = ClubFactory::new()->forOrganization($organization)->create();

        $membership = MembershipFactory::new()
            ->forClub($club)
            ->forPerson($person)
            ->create([
                'joinedAt' => new \DateTimeImmutable('2024-01-01T00:00:00+00:00'),
                'endedAt' => null,
            ]);

        $this->apiPatch('/api/v1/memberships/'.$membership->getPublicId(), [
            'endedAt' => 'not-a-date',
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testPatchRejectsOverlapWithExistingActiveMembership(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $club = ClubFactory::new()
            ->forOrganization($organization)
            ->create();

        MembershipFactory::new()
            ->forClub($club)
            ->forPerson($person)
            ->create([
                'joinedAt' => new \DateTimeImmutable('2024-01-01T00:00:00+00:00'),
                'endedAt' => null,
            ]);

        $membershipToPatch = MembershipFactory::new()
            ->forClub($club)
            ->forPerson($person)
            ->create([
                'joinedAt' => new \DateTimeImmutable('2023-01-01T00:00:00+00:00'),
                'endedAt' => new \DateTimeImmutable('2023-12-31T00:00:00+00:00'),
            ]);

        $this->apiPatch('/api/v1/memberships/'.$membershipToPatch->getPublicId(), [
            'joinedAt' => '2024-06-01T00:00:00+00:00',
            'endedAt' => null,
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testPatchRejectsOverlapWithExistingHistoricalMembership(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $club = ClubFactory::new()
            ->forOrganization($organization)
            ->create();

        MembershipFactory::new()
            ->forClub($club)
            ->forPerson($person)
            ->create([
                'joinedAt' => new \DateTimeImmutable('2024-01-01T00:00:00+00:00'),
                'endedAt' => new \DateTimeImmutable('2024-12-31T00:00:00+00:00'),
            ]);

        $membershipToPatch = MembershipFactory::new()
            ->forClub($club)
            ->forPerson($person)
            ->create([
                'joinedAt' => new \DateTimeImmutable('2023-01-01T00:00:00+00:00'),
                'endedAt' => new \DateTimeImmutable('2023-12-31T00:00:00+00:00'),
            ]);

        $this->apiPatch('/api/v1/memberships/'.$membershipToPatch->getPublicId(), [
            'joinedAt' => '2024-06-01T00:00:00+00:00',
            'endedAt' => '2024-08-31T00:00:00+00:00',
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testPatchAcceptsPeriodBeforeExistingMembershipWithoutOverlap(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $club = ClubFactory::new()
            ->forOrganization($organization)
            ->create();

        MembershipFactory::new()
            ->forClub($club)
            ->forPerson($person)
            ->create([
                'joinedAt' => new \DateTimeImmutable('2024-06-01T00:00:00+00:00'),
                'endedAt' => new \DateTimeImmutable('2024-12-31T00:00:00+00:00'),
            ]);

        $membershipToPatch = MembershipFactory::new()
            ->forClub($club)
            ->forPerson($person)
            ->create([
                'joinedAt' => new \DateTimeImmutable('2023-01-01T00:00:00+00:00'),
                'endedAt' => new \DateTimeImmutable('2023-12-31T00:00:00+00:00'),
            ]);

        $this->apiPatch('/api/v1/memberships/'.$membershipToPatch->getPublicId(), [
            'joinedAt' => '2024-01-01T00:00:00+00:00',
            'endedAt' => '2024-05-31T00:00:00+00:00',
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testPatchAcceptsPeriodAfterExistingMembershipWithoutOverlap(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $club = ClubFactory::new()
            ->forOrganization($organization)
            ->create();

        MembershipFactory::new()
            ->forClub($club)
            ->forPerson($person)
            ->create([
                'joinedAt' => new \DateTimeImmutable('2024-01-01T00:00:00+00:00'),
                'endedAt' => new \DateTimeImmutable('2024-05-31T00:00:00+00:00'),
            ]);

        $membershipToPatch = MembershipFactory::new()
            ->forClub($club)
            ->forPerson($person)
            ->create([
                'joinedAt' => new \DateTimeImmutable('2023-01-01T00:00:00+00:00'),
                'endedAt' => new \DateTimeImmutable('2023-12-31T00:00:00+00:00'),
            ]);

        $this->apiPatch('/api/v1/memberships/'.$membershipToPatch->getPublicId(), [
            'joinedAt' => '2024-06-01T00:00:00+00:00',
            'endedAt' => null,
        ]);

        $this->assertResponseIsSuccessful();
    }


    public function testPatchDoesNotDetectCurrentMembershipAsOverlap(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $club = ClubFactory::new()
            ->forOrganization($organization)
            ->create();

        $membership = MembershipFactory::new()
            ->forClub($club)
            ->forPerson($person)
            ->create([
                'joinedAt' => new \DateTimeImmutable('2024-01-01T00:00:00+00:00'),
                'endedAt' => null,
            ]);

        $this->apiPatch('/api/v1/memberships/'.$membership->getPublicId(), [
            'joinedAt' => '2024-01-02T00:00:00+00:00',
        ]);

        $this->assertResponseIsSuccessful();
    }

}
