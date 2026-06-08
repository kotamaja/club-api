<?php

namespace App\Tests\Api\ClubMembershipGroup;

use App\Factory\ClubFactory;
use App\Tests\ApiTestCase;
use App\Factory\ClubMembershipGroupFactory;

class ClubMembershipGroupDeleteTest extends ApiTestCase
{
    public function testDelete(): void
    {
        $organization =  $this->getAuthenticatedOrganizationContext()->organization;
        $club = ClubFactory::new()->forOrganization($organization)->create();
        $clubMembershipGroup = ClubMembershipGroupFactory::new()->forClub($club)->create();


        $this->apiDelete('/api/v1/club_membership_groups/' . $clubMembershipGroup->getPublicId());

        $this->assertResponseStatusCodeSame(204);

        $this->apiGet('/api/v1/club_membership_groups/' .$clubMembershipGroup->getPublicId());

        $this->assertResponseStatusCodeSame(404);
    }
}
