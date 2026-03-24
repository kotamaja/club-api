<?php

namespace App\Tests\Api\Membership;

use App\Tests\ApiTestCase;
use App\Factory\ClubFactory;
use App\Factory\MembershipFactory;
use App\Factory\PersonFactory;

class MembershipDeleteTest extends ApiTestCase
{
    public function testDelete(): void
    {
        $person = PersonFactory::createOne([
            'firstname' => 'Yves',
            'lastname' => 'Dupont',
            'email' => 'yves.dupont@example.com',
        ]);

        $club = ClubFactory::createOne([
            'name' => 'Judo Lausanne',
        ]);

        $membership = MembershipFactory::createOne([
            'person' => $person,
            'club' => $club,
            'joinedAt' => new \DateTimeImmutable('2024-01-10 10:00:00'),
            'endedAt' => null,
        ]);

        $this->apiDelete('/api/v1/memberships/' . $membership->getPublicId());

        $this->assertResponseStatusCodeSame(204);

        $this->apiGet('/api/v1/memberships/' . $membership->getPublicId());

        $this->assertResponseStatusCodeSame(404);
    }
}
