<?php

namespace App\Tests\Api\Membership;

use App\Factory\ClubFactory;
use App\Factory\MembershipFactory;
use App\Factory\PersonFactory;
use App\Tests\ApiTestCase;

class MembershipDeleteTest extends ApiTestCase
{
    public function testDelete(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create([
                'firstname' => 'Yves',
                'lastname' => 'Dupont',
                'email' => 'yves.dupont@example.com',
            ]);

        $club = ClubFactory::new()->forOrganization($organization)->create(['name' => 'Judo Lausanne',]);

        $membership = MembershipFactory::new()->
        forClub($club)->
        forPerson($person)->
        create([
            'joinedAt' => new \DateTimeImmutable('2024-01-10 10:00:00'),
            'endedAt' => null,]);


        $this->apiDelete('/api/v1/memberships/' . $membership->getPublicId());

        $this->assertResponseStatusCodeSame(204);

        $this->apiGet('/api/v1/memberships/' . $membership->getPublicId());

        $this->assertResponseStatusCodeSame(404);
    }
}
