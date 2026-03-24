<?php

namespace App\Tests\Api\Club;


use App\Tests\ApiTestCase;
use App\Factory\ClubFactory;
use App\Factory\MembershipFactory;

final class ClubDeleteTest extends ApiTestCase
{
    public function testDelete(): void
    {
        $club = ClubFactory::createOne([
            'name' => 'FC Lausanne',
        ]);

        $this->apiDelete('/api/v1/clubs/'.$club->getPublicId());

        $this->assertResponseStatusCodeSame(204);

        $this->apiGet('/api/v1/clubs/'.$club->getPublicId());

        $this->assertResponseStatusCodeSame(404);
    }


//    public function testDeleteWithMembership(): void
//    {
//        $club = ClubFactory::createOne();
//
//        MembershipFactory::createOne([
//            'club' => $club,
//        ]);
//
//
//        $this->apiDelete('/api/v1/clubs/'.$club->getPublicId());
//        $this->assertResponseStatusCodeSame(409);
//
//    }

}
