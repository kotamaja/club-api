<?php

namespace App\Tests\Api\Membership;

use App\Tests\ApiTestCase;
use App\Factory\ClubFactory;
use App\Factory\MembershipFactory;
use App\Factory\PersonFactory;

class MembershipOrderTest extends ApiTestCase
{
    public function testOrderByPersonLastnameAsc(): void
    {
        $club = ClubFactory::createOne([
            'name' => 'FC Lausanne',
        ]);

        $person1 = PersonFactory::createOne([
            'firstname' => 'Zoé',
            'lastname' => 'Bernard',
            'email' => 'zoe.bernard@example.com',
        ]);

        $person2 = PersonFactory::createOne([
            'firstname' => 'Alice',
            'lastname' => 'Martin',
            'email' => 'alice.martin@example.com',
        ]);

        $person3 = PersonFactory::createOne([
            'firstname' => 'Bob',
            'lastname' => 'Zuber',
            'email' => 'bob.zuber@example.com',
        ]);

        $membership1 = MembershipFactory::createOne([
            'person' => $person1,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-01-10 10:00:00'),
            'endedAt' => null,
        ]);

        $membership2 = MembershipFactory::createOne([
            'person' => $person2,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-01-11 10:00:00'),
            'endedAt' => null,
        ]);

        $membership3 = MembershipFactory::createOne([
            'person' => $person3,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-01-12 10:00:00'),
            'endedAt' => null,
        ]);

        $response = $this->apiGet('/api/v1/memberships?order[person.lastname]=asc');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertSame(
            [
                $membership1->getPublicId(),
                $membership2->getPublicId(),
                $membership3->getPublicId(),
            ],
            $this->extractIds($data)
        );
    }

    public function testOrderByPersonFirstnameDesc(): void
    {
        $club = ClubFactory::createOne([
            'name' => 'FC Lausanne',
        ]);

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

        $person3 = PersonFactory::createOne([
            'firstname' => 'Zoé',
            'lastname' => 'Bernard',
            'email' => 'zoe.bernard@example.com',
        ]);

        $membership1 = MembershipFactory::createOne([
            'person' => $person1,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-01-10 10:00:00'),
            'endedAt' => null,
        ]);

        $membership2 = MembershipFactory::createOne([
            'person' => $person2,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-01-11 10:00:00'),
            'endedAt' => null,
        ]);

        $membership3 = MembershipFactory::createOne([
            'person' => $person3,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-01-12 10:00:00'),
            'endedAt' => null,
        ]);

        $response = $this->apiGet('/api/v1/memberships?order[person.firstname]=desc');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertSame(
            [
                $membership3->getPublicId(),
                $membership2->getPublicId(),
                $membership1->getPublicId(),
            ],
            $this->extractIds($data)
        );
    }

    public function testOrderByClubNameAsc(): void
    {
        $person = PersonFactory::createOne([
            'firstname' => 'Alice',
            'lastname' => 'Martin',
            'email' => 'alice.martin@example.com',
        ]);

        $club1 = ClubFactory::createOne(['name' => 'Alpha Club']);
        $club2 = ClubFactory::createOne(['name' => 'Beta Club']);
        $club3 = ClubFactory::createOne(['name' => 'Gamma Club']);

        $membership1 = MembershipFactory::createOne([
            'person' => $person,
            'club' => $club2,
            'joinedAt' => new \DateTimeImmutable('2024-01-10 10:00:00'),
            'endedAt' => null,
        ]);

        $membership2 = MembershipFactory::createOne([
            'person' => $person,
            'club' => $club3,
            'joinedAt' => new \DateTimeImmutable('2024-01-11 10:00:00'),
            'endedAt' => null,
        ]);

        $membership3 = MembershipFactory::createOne([
            'person' => $person,
            'club' => $club1,
            'joinedAt' => new \DateTimeImmutable('2024-01-12 10:00:00'),
            'endedAt' => null,
        ]);

        $response = $this->apiGet('/api/v1/memberships?order[club.name]=asc');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertSame(
            [
                $membership3->getPublicId(),
                $membership1->getPublicId(),
                $membership2->getPublicId(),
            ],
            $this->extractIds($data)
        );
    }

    public function testOrderByJoinedAtDesc(): void
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

        $membership3 = MembershipFactory::createOne([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-03-10 10:00:00'),
            'endedAt' => null,
        ]);

        $response = $this->apiGet('/api/v1/memberships?order[joinedAt]=desc');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertSame(
            [
                $membership3->getPublicId(),
                $membership2->getPublicId(),
                $membership1->getPublicId(),
            ],
            $this->extractIds($data)
        );
    }

    private function extractIds(array $data): array
    {
        return array_map(
            static fn(array $item): string => $item['id'],
            $data['items']
        );
    }
}
