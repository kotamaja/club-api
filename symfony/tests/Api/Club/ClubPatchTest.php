<?php

namespace App\Tests\Api\Club;

use App\Tests\ApiTestCase;
use App\Tests\Factory\ClubFactory;

final class ClubPatchTest extends ApiTestCase
{
    public function testPatch(): void
    {
        $club = ClubFactory::createOne([
            'name' => 'FC Lausanne',
        ]);

        $response = $this->apiPatch('/api/v1/clubs/'.$club->getPublicId(), [
            'name' => 'FC Lausanne-Sport',
        ]);

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertArrayHasValidUlid($data, 'id');

        $this->assertSame($club->getPublicId(), $data['id']);

        $this->assertSame('FC Lausanne-Sport', $data['name']);
    }

    public function testPatchValidation(): void
    {
        $club = ClubFactory::createOne([
            'name' => 'FC Lausanne',
        ]);

        $this->apiPatch('/api/v1/clubs/'.$club->getPublicId(), [
            'name' => '',
        ]);

        $this->assertResponseStatusCodeSame(422);
    }
}
