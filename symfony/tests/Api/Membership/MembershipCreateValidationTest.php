<?php

namespace App\Tests\Api\Membership;

use App\Tests\ApiTestCase;
use App\Factory\ClubFactory;
use App\Factory\PersonFactory;

final class MembershipCreateValidationTest extends ApiTestCase
{
    public function testCreateRejectsBlankPersonId(): void
    {
        $club = ClubFactory::createOne();

        $this->apiPost('/api/v1/memberships', [
            'personId' => '',
            'clubId' => $club->getPublicId(),
            'joinedAt' => '2024-01-01T00:00:00+00:00',
            'endedAt' => null,
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateRejectsBlankClubId(): void
    {
        $person = PersonFactory::createOne();

        $this->apiPost('/api/v1/memberships', [
            'personId' => $person->getPublicId(),
            'clubId' => '',
            'joinedAt' => '2024-01-01T00:00:00+00:00',
            'endedAt' => null,
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateRejectsInvalidPersonId(): void
    {
        $club = ClubFactory::createOne();

        $this->apiPost('/api/v1/memberships', [
            'personId' => 'not-a-valid-ulid',
            'clubId' => $club->getPublicId(),
            'joinedAt' => '2024-01-01T00:00:00+00:00',
            'endedAt' => null,
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateRejectsInvalidClubId(): void
    {
        $person = PersonFactory::createOne();

        $this->apiPost('/api/v1/memberships', [
            'personId' => $person->getPublicId(),
            'clubId' => 'not-a-valid-ulid',
            'joinedAt' => '2024-01-01T00:00:00+00:00',
            'endedAt' => null,
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateRejectsNullJoinedAt(): void
    {
        $person = PersonFactory::createOne();
        $club = ClubFactory::createOne();

        $this->apiPost('/api/v1/memberships', [
            'personId' => $person->getPublicId(),
            'clubId' => $club->getPublicId(),
            'joinedAt' => null,
            'endedAt' => null,
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateRejectsEndedAtBeforeJoinedAt(): void
    {
        $person = PersonFactory::createOne();
        $club = ClubFactory::createOne();

        $this->apiPost('/api/v1/memberships', [
            'personId' => $person->getPublicId(),
            'clubId' => $club->getPublicId(),
            'joinedAt' => '2024-12-31T00:00:00+00:00',
            'endedAt' => '2024-01-01T00:00:00+00:00',
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateRejectsUnknownPerson(): void
    {
        $club = ClubFactory::createOne();

        $this->apiPost('/api/v1/memberships', [
            'personId' => '01ARZ3NDEKTSV4RRFFQ69G5FAV',
            'clubId' => $club->getPublicId(),
            'joinedAt' => '2024-01-01T00:00:00+00:00',
            'endedAt' => null,
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateRejectsUnknownClub(): void
    {
        $person = PersonFactory::createOne();

        $this->apiPost('/api/v1/memberships', [
            'personId' => $person->getPublicId(),
            'clubId' => '01ARZ3NDEKTSV4RRFFQ69G5FAV',
            'joinedAt' => '2024-01-01T00:00:00+00:00',
            'endedAt' => null,
        ]);

        $this->assertResponseStatusCodeSame(404);
    }
}
