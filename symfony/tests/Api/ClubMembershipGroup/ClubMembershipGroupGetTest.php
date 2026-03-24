<?php

namespace App\Tests\Api\ClubMembershipGroup;

use App\Tests\ApiTestCase;
use App\Factory\ClubFactory;
use App\Factory\ClubMembershipGroupFactory;
use Symfony\Component\Uid\Ulid;

class ClubMembershipGroupGetTest extends ApiTestCase
{
    public function testGetCollection(): void
    {
        $club = ClubFactory::createOne();

        $clubMembershipGroup = ClubMembershipGroupFactory::createMany(3, [
            'club' => $club,

        ]);

        $expectedIds = array_map(
            fn($p) => $p->getPublicId(),
            $clubMembershipGroup
        );

        $response = $this->apiGet('/api/v1/club_membership_groups');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertSame(3, $data['pagination']['totalItems']);
        $this->assertCount(3, $data['items']);

        $returnedIds = array_map(
            fn($item) => $item['id'],
            $data['items']
        );

        foreach ($returnedIds as $id) {
            $this->assertTrue(Ulid::isValid($id));
        }

        $this->assertEqualsCanonicalizing($expectedIds, $returnedIds);
    }


    public function testGetItem(): void
    {
        $club = ClubFactory::createOne();

        $clubMembershipGroup = ClubMembershipGroupFactory::createOne([
            'name' => 'test',
            'description' => 'test description',
            'club' => $club,
        ]);

        $response = $this->apiGet('/api/v1/club_membership_groups/' . $clubMembershipGroup->getPublicId());

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();


        $this->assertArrayHasValidUlid($data, 'id');
        $this->assertSame($clubMembershipGroup->getPublicId(), $data['id']);

        $this->assertArrayHasKey('club', $data);
        $this->assertIsArray($data['club']);
        $this->assertArrayHasValidUlid($data['club'], 'id');
        $this->assertSame($club->getPublicId(), $data['club']['id']);
        $this->assertSame($club->getName(), $data['club']['name']);


    }

}
