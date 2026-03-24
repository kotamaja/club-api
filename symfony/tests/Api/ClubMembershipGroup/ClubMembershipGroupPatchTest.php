<?php

namespace App\Tests\Api\ClubMembershipGroup;

use App\Tests\ApiTestCase;
use App\Factory\ClubFactory;
use App\Factory\ClubMembershipGroupFactory;

class ClubMembershipGroupPatchTest extends ApiTestCase
{

    public function testPatch(): void
    {
        $club = ClubFactory::createOne();

        $clubMembershipGroup = ClubMembershipGroupFactory::createOne([
            'name' => "old name",
            'description' => "old description",
            'club' => $club,

        ]);

        $newClub = ClubFactory::createOne();

        $response = $this->apiPatch('/api/v1/club_membership_groups/' . $clubMembershipGroup->getPublicId(), [
            'name' => 'new name',
            'description' => 'new description',
            'clubId'=> $newClub->getPublicId(),
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
        $this->assertArrayHasValidUlid($data['club'], 'id');
        $this->assertSame($newClub->getPublicId(), $data['club']['id']);

    }


}
