<?php

namespace App\Tests\Api\PersonContact;

use App\Enum\RelationshipType;
use App\Tests\ApiTestCase;
use App\Factory\PersonContactFactory;

final class PersonContactPatchTest extends ApiTestCase
{
    public function testPatch(): void
    {
        $contact = PersonContactFactory::createOne([
            'type' => RelationshipType::PARENT,
            'isEmergencyContact' => false,
        ]);

        $response = $this->apiPatch(
            '/api/v1/person_contacts/'.$contact->getPublicId(),
            [
                'type' => 'legal_guardian',
                'isEmergencyContact' => true,
            ]
        );

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertArrayHasValidUlid($data, 'id');

        $this->assertJsonContains([
            'type' => 'legal_guardian',
            'isEmergencyContact' => true,
        ]);
    }
}
