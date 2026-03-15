<?php

namespace App\Tests\Api\Club;

use App\Tests\ApiTestCase;

final class ClubCreateValidationTest extends ApiTestCase
{
    public function testCreateRejectsBlankName(): void
    {
        $this->apiPost('/api/v1/clubs', [
            'name' => '',
        ]);

        $this->assertResponseStatusCodeSame(422);
    }
}
