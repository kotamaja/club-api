<?php

namespace App\Tests\Api\Membership;

use App\Factory\ClubFactory;
use App\Factory\MembershipFactory;
use App\Factory\PersonFactory;
use App\Tests\ApiTestCase;

class MembershipFilterTest extends ApiTestCase
{
    public function testFilterByPersonId(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $targetPerson = PersonFactory::new()->forOrganization($organization)->create([
            'firstname' => 'Alice',
            'lastname' => 'Martin',
            'email' => 'alice.martin@example.com',
        ]);

        $otherPerson = PersonFactory::new()->forOrganization($organization)->create([
            'firstname' => 'Bob',
            'lastname' => 'Durand',
            'email' => 'bob.durand@example.com',
        ]);

        $club1 = ClubFactory::new()->forOrganization($organization)->create(['name' => 'FC Lausanne']);
        $club2 = ClubFactory::new()->forOrganization($organization)->create(['name' => 'FC Sion']);

        $membership1 = MembershipFactory::new()->forPerson($targetPerson)->forClub($club1)->create([
            'joinedAt' => new \DateTimeImmutable('2024-01-10 10:00:00'),
            'endedAt' => null,
        ]);

        $membership2 = MembershipFactory::new()->forPerson($targetPerson)->forClub($club2)->create([
            'joinedAt' => new \DateTimeImmutable('2024-02-10 10:00:00'),
            'endedAt' => new \DateTimeImmutable('2024-06-01 10:00:00'),
        ]);

        MembershipFactory::new()->forPerson($otherPerson)->forClub($club1)->create([
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
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

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

        $targetClub = ClubFactory::new()->forOrganization($organization)->create([
            'name' => 'FC Lausanne',
        ]);

        $otherClub = ClubFactory::new()->forOrganization($organization)->create([
            'name' => 'FC Sion',
        ]);

        $membership = MembershipFactory::new()->forPerson($person1)->forClub($targetClub)->create([
            'joinedAt' => new \DateTimeImmutable('2024-01-10 10:00:00'),
            'endedAt' => null,
        ]);

        MembershipFactory::new()->forPerson($person2)->forClub($otherClub)->create([
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
            'joinedAt' => new \DateTimeImmutable('2024-01-10 10:00:00'),
            'endedAt' => null,
        ]);

        $membership2 = MembershipFactory::new()->forPerson($person)->forClub($club)->create([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-02-10 10:00:00'),
            'endedAt' => null,
        ]);

        MembershipFactory::new()->forPerson($person)->forClub($club)->create([
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
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person = PersonFactory::new()->forOrganization($organization)->create([
            'firstname' => 'Alice',
            'lastname' => 'Martin',
            'email' => 'alice.martin@example.com',
        ]);

        $club = ClubFactory::new()->forOrganization($organization)->create([
            'name' => 'FC Lausanne',
        ]);

        MembershipFactory::new()->forPerson($person)->forClub($club)->create([
            'joinedAt' => new \DateTimeImmutable('2024-01-10 10:00:00'),
            'endedAt' => null,
        ]);

        $membership2 = MembershipFactory::new()->forPerson($person)->forClub($club)->create([
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
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person = PersonFactory::new()->forOrganization($organization)->create([
            'firstname' => 'Alice',
            'lastname' => 'Martin',
            'email' => 'alice.martin@example.com',
        ]);

        $club = ClubFactory::new()->forOrganization($organization)->create([
            'name' => 'FC Lausanne',
        ]);

        $endedMembership = MembershipFactory::new()->forPerson($person)->forClub($club)->create([
            'joinedAt' => new \DateTimeImmutable('2024-01-10 10:00:00'),
            'endedAt' => new \DateTimeImmutable('2024-04-01 10:00:00'),
        ]);

        MembershipFactory::new()->forPerson($person)->forClub($club)->create([
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
