<?php

namespace App\Tests\Api\Membership;

use App\Tests\ApiTestCase;
use App\Factory\ClubFactory;
use App\Factory\MembershipFactory;
use App\Factory\MembershipGroupFactory;
use App\Factory\PersonFactory;

class MembershipFilterTest extends ApiTestCase
{
    public function testFilterByPersonId(): void
    {
        $targetPerson = PersonFactory::createOne([
            'firstname' => 'Alice',
            'lastname' => 'Martin',
            'email' => 'alice.martin@example.com',
        ]);

        $otherPerson = PersonFactory::createOne([
            'firstname' => 'Bob',
            'lastname' => 'Durand',
            'email' => 'bob.durand@example.com',
        ]);

        $club1 = ClubFactory::createOne(['name' => 'FC Lausanne']);
        $club2 = ClubFactory::createOne(['name' => 'FC Sion']);

        $membership1 = MembershipFactory::createOne([
            'person' => $targetPerson,
            'club' => $club1,
            'joinedAt' => new \DateTimeImmutable('2024-01-10 10:00:00'),
            'endedAt' => null,
        ]);

        $membership2 = MembershipFactory::createOne([
            'person' => $targetPerson,
            'club' => $club2,
            'joinedAt' => new \DateTimeImmutable('2024-02-10 10:00:00'),
            'endedAt' => new \DateTimeImmutable('2024-06-01 10:00:00'),
        ]);

        MembershipFactory::createOne([
            'person' => $otherPerson,
            'club' => $club1,
            'joinedAt' => new \DateTimeImmutable('2024-03-10 10:00:00'),
            'endedAt' => null,
        ]);

        $response = $this->apiGet('/api/v1/memberships?personId[]=' . $targetPerson->getPublicId());

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertEqualsCanonicalizing(
            [$membership1->getPublicId(), $membership2->getPublicId()],
            $this->extractIds($data)
        );
    }

    public function testFilterByClubId(): void
    {
        $person1 = PersonFactory::createOne([
            'firstname' => 'Alice',
            'lastname' => 'Martin',
            'email' => 'alice.martin@example.com',
        ]);

        $person2 = PersonFactory::createOne([
            'firstname' => 'Bob',
            'lastname' => 'Durand',
            'email' => 'bob.durand@example.com',
        ]);

        $targetClub = ClubFactory::createOne([
            'name' => 'FC Lausanne',
        ]);

        $otherClub = ClubFactory::createOne([
            'name' => 'FC Sion',
        ]);

        $membership = MembershipFactory::createOne([
            'person' => $person1,
            'club' => $targetClub,
            'joinedAt' => new \DateTimeImmutable('2024-01-10 10:00:00'),
            'endedAt' => null,
        ]);

        MembershipFactory::createOne([
            'person' => $person2,
            'club' => $otherClub,
            'joinedAt' => new \DateTimeImmutable('2024-02-10 10:00:00'),
            'endedAt' => null,
        ]);

        $response = $this->apiGet('/api/v1/memberships?clubId[]=' . $targetClub->getPublicId());

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertSame([$membership->getPublicId()], $this->extractIds($data));
    }

    public function testFilterByIdArray(): void
    {
        $person = PersonFactory::createOne([
            'firstname' => 'Alice',
            'lastname' => 'Martin',
            'email' => 'alice.martin@example.com',
        ]);

        $club = ClubFactory::createOne([
            'name' => 'FC Lausanne',
        ]);

        $membership1 = MembershipFactory::createOne([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-01-10 10:00:00'),
            'endedAt' => null,
        ]);

        $membership2 = MembershipFactory::createOne([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-02-10 10:00:00'),
            'endedAt' => null,
        ]);

        MembershipFactory::createOne([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-03-10 10:00:00'),
            'endedAt' => null,
        ]);

        $response = $this->apiGet(sprintf(
            '/api/v1/memberships?id[]=%s&id[]=%s',
            $membership1->getPublicId(),
            $membership2->getPublicId()
        ));

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertEqualsCanonicalizing(
            [$membership1->getPublicId(), $membership2->getPublicId()],
            $this->extractIds($data)
        );
    }

    public function testFilterByJoinedAtAfter(): void
    {
        $person = PersonFactory::createOne([
            'firstname' => 'Alice',
            'lastname' => 'Martin',
            'email' => 'alice.martin@example.com',
        ]);

        $club = ClubFactory::createOne([
            'name' => 'FC Lausanne',
        ]);

        MembershipFactory::createOne([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-01-10 10:00:00'),
            'endedAt' => null,
        ]);

        $membership2 = MembershipFactory::createOne([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-03-10 10:00:00'),
            'endedAt' => null,
        ]);

        $response = $this->apiGet('/api/v1/memberships?joinedAt[after]=2024-02-01');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertSame([$membership2->getPublicId()], $this->extractIds($data));
    }

    public function testFilterByEndedAtBeforeExcludesNullValues(): void
    {
        $person = PersonFactory::createOne([
            'firstname' => 'Alice',
            'lastname' => 'Martin',
            'email' => 'alice.martin@example.com',
        ]);

        $club = ClubFactory::createOne([
            'name' => 'FC Lausanne',
        ]);

        $endedMembership = MembershipFactory::createOne([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-01-10 10:00:00'),
            'endedAt' => new \DateTimeImmutable('2024-04-01 10:00:00'),
        ]);

        MembershipFactory::createOne([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-02-10 10:00:00'),
            'endedAt' => null,
        ]);

        $response = $this->apiGet('/api/v1/memberships?endedAt[before]=2024-05-01');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertSame([$endedMembership->getPublicId()], $this->extractIds($data));
    }



    private function extractIds(array $data): array
    {
        return array_map(
            static fn(array $item): string => $item['id'],
            $data['items']
        );
    }
}
