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
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $club = ClubFactory::new()->forOrganization($organization)->create([
            'name' => 'FC Lausanne',
        ]);

        $person1 = PersonFactory::new()->forOrganization($organization)->create([
            'firstname' => 'Zoé',
            'lastname' => 'Bernard',
            'email' => 'zoe.bernard@example.com',
        ]);

        $person2 =  PersonFactory::new()->forOrganization($organization)->create([
            'firstname' => 'Alice',
            'lastname' => 'Martin',
            'email' => 'alice.martin@example.com',
        ]);

        $person3 =  PersonFactory::new()->forOrganization($organization)->create([
            'firstname' => 'Bob',
            'lastname' => 'Zuber',
            'email' => 'bob.zuber@example.com',
        ]);

        $membership1 = MembershipFactory::new()->forPerson($person1)->forClub($club)->create([
            'joinedAt' => new \DateTimeImmutable('2024-01-10 10:00:00'),
            'endedAt' => null,
        ]);

        $membership2 = MembershipFactory::new()->forPerson($person2)->forClub($club)->create([
            'joinedAt' => new \DateTimeImmutable('2024-01-11 10:00:00'),
            'endedAt' => null,
        ]);

        $membership3 =  MembershipFactory::new()->forPerson($person3)->forClub($club)->create([
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
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $club =  ClubFactory::new()->forOrganization($organization)->create([
            'name' => 'FC Lausanne',
        ]);

        $person1 = PersonFactory::new()->forOrganization($organization)->create([
            'firstname' => 'Alice',
            'lastname' => 'Martin',
            'email' => 'alice.martin@example.com',
        ]);

        $person2 = PersonFactory::new()->forOrganization($organization)->create([
            'firstname' => 'Bob',
            'lastname' => 'Durand',
            'email' => 'bob.durand@example.com',
        ]);

        $person3 = PersonFactory::new()->forOrganization($organization)->create([
            'firstname' => 'Zoé',
            'lastname' => 'Bernard',
            'email' => 'zoe.bernard@example.com',
        ]);

        $membership1 =  MembershipFactory::new()->forPerson($person1)->forClub($club)->create([
            'person' => $person1,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-01-10 10:00:00'),
            'endedAt' => null,
        ]);

        $membership2 = MembershipFactory::new()->forPerson($person2)->forClub($club)->create([
            'person' => $person2,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-01-11 10:00:00'),
            'endedAt' => null,
        ]);

        $membership3 =  MembershipFactory::new()->forPerson($person3)->forClub($club)->create([
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
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person =PersonFactory::new()->forOrganization($organization)->create([
            'firstname' => 'Alice',
            'lastname' => 'Martin',
            'email' => 'alice.martin@example.com',
        ]);

        $club1 = ClubFactory::new()->forOrganization($organization)->create(['name' => 'Alpha Club']);
        $club2 = ClubFactory::new()->forOrganization($organization)->create(['name' => 'Beta Club']);
        $club3 = ClubFactory::new()->forOrganization($organization)->create(['name' => 'Gamma Club']);

        $membership1 = MembershipFactory::new()->forPerson($person)->forClub($club2)->create([
            'joinedAt' => new \DateTimeImmutable('2024-01-10 10:00:00'),
            'endedAt' => null,
        ]);

        $membership2 = MembershipFactory::new()->forPerson($person)->forClub($club3)->create([
            'joinedAt' => new \DateTimeImmutable('2024-01-11 10:00:00'),
            'endedAt' => null,
        ]);

        $membership3 = MembershipFactory::new()->forPerson($person)->forClub($club1)->create([
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

        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person = PersonFactory::new()->forOrganization($organization)->create([
            'firstname' => 'Alice',
            'lastname' => 'Martin',
            'email' => 'alice.martin@example.com',
        ]);

        $club = ClubFactory::new()->forOrganization($organization)->create([
            'name' => 'FC Lausanne',
        ]);

        $membership1 = MembershipFactory::new()->forPerson($person)->forClub($club)->create([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-01-10 10:00:00'),
            'endedAt' => null,
        ]);

        $membership2 = MembershipFactory::new()->forPerson($person)->forClub($club)->create([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-02-10 10:00:00'),
            'endedAt' => null,
        ]);

        $membership3 = MembershipFactory::new()->forPerson($person)->forClub($club)->create([
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
