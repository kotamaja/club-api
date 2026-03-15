<?php

namespace App\Tests\Api\Club;

use App\Tests\ApiTestCase;

final class ClubCreateTest extends ApiTestCase
{
    public function testCreate(): void
    {
        $response = $this->apiPost('/api/v1/clubs', [
            'name' => 'FC Basel',
        ]);

        $this->assertResponseStatusCodeSame(201);

        $data = $response->toArray();

        $this->assertSame('FC Basel', $data['name']);
        $this->assertArrayHasValidUlid($data, 'id');
    }

    public function testCreateValidation(): void
    {
        $this->apiPost('/api/v1/clubs', [
            'name' => '',
        ]);

        $this->assertResponseStatusCodeSame(422);
    }
}
