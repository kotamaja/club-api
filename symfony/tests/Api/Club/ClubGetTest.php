<?php

namespace App\Tests\Api\Club;

use App\Tests\ApiTestCase;
use App\Tests\Factory\ClubFactory;

final class ClubGetTest extends ApiTestCase
{
    public function testGetCollection(): void
    {
        ClubFactory::createMany(3);

        $response = $this->apiGet('/api/v1/clubs');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertSame(3, $data['pagination']['totalItems']);
    }

    public function testGetItem(): void
    {
        $club = ClubFactory::createOne([
            'name' => 'FC Lausanne',
        ]);

        $response = $this->apiGet('/api/v1/clubs/'.$club->getPublicId());

        $this->assertResponseIsSuccessful();

        $this->assertJsonContains([
            'name' => 'FC Lausanne',
        ]);
    }
}
