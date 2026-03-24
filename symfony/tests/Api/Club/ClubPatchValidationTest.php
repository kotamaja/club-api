<?php

namespace App\Tests\Api\Club;

use App\Factory\ClubFactory;
use App\Tests\ApiTestCase;

final class ClubPatchValidationTest extends ApiTestCase
{
    public function testPatchRejectsBlankName(): void
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
