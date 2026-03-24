<?php

namespace App\Tests\Api\Membership;

use App\Tests\ApiTestCase;
use App\Factory\ClubFactory;
use App\Factory\MembershipFactory;
use App\Factory\PersonFactory;

final class MembershipGetTest extends ApiTestCase
{
    public function testGetCollection(): void
    {
        $person = PersonFactory::createOne();
        $club = ClubFactory::createOne();

        $membership = MembershipFactory::createOne([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-01-01T00:00:00+00:00'),
            'endedAt' => null,
        ]);

        $response = $this->apiGet('/api/v1/memberships');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertSame(1, $data['pagination']['totalItems']);
        $this->assertCount(1, $data['items']);

        $item = $data['items'][0];

        $this->assertArrayHasValidUlid($item, 'id');
        $this->assertArrayHasValidUlid($item, 'personId');
        $this->assertArrayHasValidUlid($item, 'clubId');

        $this->assertSame($membership->getPublicId(), $item['id']);
        $this->assertSame($person->getPublicId(), $item['personId']);
        $this->assertSame($person->getLastname(), $item['personLastName']);
        $this->assertSame($person->getFirstname(), $item['personFirstName']);

        $this->assertSame($club->getPublicId(), $item['clubId']);
        $this->assertSame($club->getName(), $item['clubName']);
        $this->assertApiDateTimeSameLocal('2024-01-01T00:00:00+00:00', $item['joinedAt']);
        $this->assertNull($item['endedAt']);
        $this->assertTrue($item['isActive']);
    }

    public function testGetItem(): void
    {
        $person = PersonFactory::createOne();
        $club = ClubFactory::createOne();

        $membership = MembershipFactory::createOne([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-01-01T00:00:00+00:00'),
            'endedAt' => new \DateTimeImmutable('2024-02-01T00:00:00+00:00'),
        ]);

        $response = $this->apiGet('/api/v1/memberships/'.$membership->getPublicId());

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertArrayHasValidUlid($data, 'id');
        $this->assertSame($membership->getPublicId(), $data['id']);

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



        $this->assertApiDateTimeSameLocal('2024-01-01T00:00:00+00:00', $data['joinedAt']);
        $this->assertApiDateTimeSameLocal('2024-02-01T00:00:00+00:00', $data['endedAt']);
        $this->assertFalse($data['isActive']);
    }
}
