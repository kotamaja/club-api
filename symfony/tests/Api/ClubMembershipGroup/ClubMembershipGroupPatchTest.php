<?php

namespace App\Tests\Api\ClubMembershipGroup;

use App\Factory\ClubFactory;
use App\Factory\ClubMembershipGroupFactory;
use App\Tests\ApiTestCase;

class ClubMembershipGroupPatchTest extends ApiTestCase
{

    public function testPatch(): void
    {


        $organization = $this->getAuthenticatedOrganizationContext()->organization;
        $club = ClubFactory::new()->forOrganization($organization)->create();
        $clubMembershipGroup = ClubMembershipGroupFactory::new()->forClub($club)->create(['name' => "old name", 'description' => "old description",]);

        $newClub = ClubFactory::new()->forOrganization($organization)->create();

        $response = $this->apiPatch('/api/v1/club_membership_groups/' . $clubMembershipGroup->getPublicId(), [
            'name' => 'new name',
            'description' => 'new description',
        ]);

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertArrayHasValidUlid($data, 'id');

        $this->assertSame($clubMembershipGroup->getPublicId(), $data['id']);

        $this->assertJsonContains([
            'name' => 'new name',
            'description' => 'new description',
        ]);

        $this->assertArrayHasKey('club', $data);
        $this->assertIsArray($data['club']);

    }


}
