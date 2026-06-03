<?php

namespace App\Tests\Api\Club;

use App\Tests\ApiTestCase;
use App\Factory\ClubFactory;

final class ClubGetTest extends ApiTestCase
{
    public function testGetCollection(): void
    {
        $organization =  $this->getAuthenticatedOrganizationContext()->organization;
        ClubFactory::new()->forOrganization($organization)->many(3)->create();

        $response = $this->apiGet('/api/v1/clubs');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertSame(3, $data['pagination']['totalItems']);
    }

    public function testGetItem(): void
    {
        $organization =  $this->getAuthenticatedOrganizationContext()->organization;
        $club = ClubFactory::new()->forOrganization($organization)->create(['name' => 'FC Lausanne']);

        $response = $this->apiGet('/api/v1/clubs/'.$club->getPublicId());

        $this->assertResponseIsSuccessful();

        $this->assertJsonContains([
            'name' => 'FC Lausanne',
        ]);
    }
}
