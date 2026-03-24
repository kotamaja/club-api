<?php

namespace App\Tests\Api\Membership;

use App\Tests\ApiTestCase;
use App\Factory\ClubFactory;
use App\Factory\PersonFactory;

final class MembershipCreateTest extends ApiTestCase
{
    public function testCreateActiveMembership(): void
    {
        $person = PersonFactory::createOne();
        $club = ClubFactory::createOne();

        $response = $this->apiPost('/api/v1/memberships', [
            'personId' => $person->getPublicId(),
            'clubId' => $club->getPublicId(),
            'joinedAt' => '2024-01-01T00:00:00+00:00',
            'endedAt' => null,
        ]);

        $this->assertResponseStatusCodeSame(201);

        $data = $response->toArray();

        $this->assertArrayHasValidUlid($data, 'id');

        $this->assertArrayHasKey('person', $data);
        $this->assertIsArray($data['person']);
        $this->assertArrayHasValidUlid($data['person'], 'id');
        $this->assertSame($person->getPublicId(), $data['person']['id']);
        $this->assertSame($person->getFirstname(), $data['person']['firstname']);
        $this->assertSame($person->getLastname(), $data['person']['lastname']);
        $this->assertSame($person->getEmail(), $data['person']['email']);

        $this->assertArrayHasKey('club', $data);
        $this->assertIsArray($data['club']);
        $this->assertArrayHasValidUlid($data['club'], 'id');
        $this->assertSame($club->getPublicId(), $data['club']['id']);
        $this->assertSame($club->getName(), $data['club']['name']);

        $this->assertSame('2024-01-01T00:00:00+00:00', $data['joinedAt']);
        $this->assertNull($data['endedAt']);
        $this->assertTrue($data['isActive']);
    }

    public function testCreateHistoricalMembership(): void
    {
        $person = PersonFactory::createOne();
        $club = ClubFactory::createOne();

        $response = $this->apiPost('/api/v1/memberships', [
            'personId' => $person->getPublicId(),
            'clubId' => $club->getPublicId(),
            'joinedAt' => '2023-01-01T00:00:00+00:00',
            'endedAt' => '2023-12-31T00:00:00+00:00',
        ]);

        $this->assertResponseStatusCodeSame(201);

        $data = $response->toArray();

        $this->assertArrayHasValidUlid($data, 'id');


        $this->assertArrayHasKey('person', $data);
        $this->assertIsArray($data['person']);
        $this->assertArrayHasValidUlid($data['person'], 'id');
        $this->assertSame($person->getPublicId(), $data['person']['id']);
        $this->assertSame($person->getFirstname(), $data['person']['firstname']);
        $this->assertSame($person->getLastname(), $data['person']['lastname']);
        $this->assertSame($person->getEmail(), $data['person']['email']);

        $this->assertArrayHasKey('club', $data);
        $this->assertIsArray($data['club']);
        $this->assertArrayHasValidUlid($data['club'], 'id');
        $this->assertSame($club->getPublicId(), $data['club']['id']);
        $this->assertSame($club->getName(), $data['club']['name']);


        $this->assertSame('2023-01-01T00:00:00+00:00', $data['joinedAt']);
        $this->assertSame('2023-12-31T00:00:00+00:00', $data['endedAt']);
        $this->assertFalse($data['isActive']);
    }

    public function testCreateRejectsSecondActiveMembershipForSamePersonAndClub(): void
    {
        $person = PersonFactory::createOne();
        $club = ClubFactory::createOne();

        $this->apiPost('/api/v1/memberships', [
            'personId' => $person->getPublicId(),
            'clubId' => $club->getPublicId(),
            'joinedAt' => '2024-01-01T00:00:00+00:00',
            'endedAt' => null,
        ]);

        $this->assertResponseStatusCodeSame(201);

        $this->apiPost('/api/v1/memberships', [
            'personId' => $person->getPublicId(),
            'clubId' => $club->getPublicId(),
            'joinedAt' => '2024-02-01T00:00:00+00:00',
            'endedAt' => null,
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateAllowsHistoricalMembershipEvenIfActiveMembershipExists(): void
    {
        $person = PersonFactory::createOne();
        $club = ClubFactory::createOne();

        $this->apiPost('/api/v1/memberships', [
            'personId' => $person->getPublicId(),
            'clubId' => $club->getPublicId(),
            'joinedAt' => '2024-01-01T00:00:00+00:00',
            'endedAt' => null,
        ]);

        $this->assertResponseStatusCodeSame(201);

        $response = $this->apiPost('/api/v1/memberships', [
            'personId' => $person->getPublicId(),
            'clubId' => $club->getPublicId(),
            'joinedAt' => '2023-01-01T00:00:00+00:00',
            'endedAt' => '2023-12-31T00:00:00+00:00',
        ]);

        $this->assertResponseStatusCodeSame(201);

        $data = $response->toArray();

        $this->assertArrayHasValidUlid($data, 'id');
        $this->assertSame('2023-01-01T00:00:00+00:00', $data['joinedAt']);
        $this->assertSame('2023-12-31T00:00:00+00:00', $data['endedAt']);
        $this->assertFalse($data['isActive']);
    }
}
