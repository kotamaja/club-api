<?php

namespace App\Tests\Api\ClubMembershipGroup;

use App\Tests\ApiTestCase;
use App\Factory\ClubMembershipGroupFactory;

class ClubMembershipGroupDeleteTest extends ApiTestCase
{
    public function testDelete(): void
    {
        $club = ClubMembershipGroupFactory::createOne([
            'name' => 'Judo Lausanne',
        ]);

        $this->apiDelete('/api/v1/club_membership_groups/' . $club->getPublicId());

        $this->assertResponseStatusCodeSame(204);

        $this->apiGet('/api/v1/club_membership_groups/' . $club->getPublicId());

        $this->assertResponseStatusCodeSame(404);
    }
}
