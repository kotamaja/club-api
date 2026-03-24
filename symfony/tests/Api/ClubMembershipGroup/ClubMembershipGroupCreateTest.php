<?php

namespace App\Tests\Api\ClubMembershipGroup;

use App\Tests\ApiTestCase;
use App\Factory\ClubFactory;

class ClubMembershipGroupCreateTest  extends ApiTestCase
{
    public function testCreate(): void
    {
        $club = ClubFactory::createOne();

        $response = $this->apiPost('/api/v1/club_membership_groups', [
            'clubId' => $club->getPublicId(),
            'name' => 'test',
            'description' => 'test description',
        ]);

        $this->assertResponseStatusCodeSame(201);

        $data = $response->toArray();

        $this->assertArrayHasValidUlid($data, 'id');


        $this->assertArrayHasKey('club', $data);
        $this->assertIsArray($data['club']);
        $this->assertArrayHasValidUlid($data['club'], 'id');
        $this->assertSame($club->getPublicId(), $data['club']['id']);
        $this->assertSame($club->getName(), $data['club']['name']);

        $this->assertSame('test', $data['name']);
        $this->assertSame( 'test description', $data['description']);
    }
}
