<?php

namespace App\Tests\Api\PersonContact;

use App\Enum\RelationshipType;
use App\Factory\PersonFactory;
use App\Tests\ApiTestCase;
use App\Factory\PersonContactFactory;

final class PersonContactPatchTest extends ApiTestCase
{
    public function testPatch(): void
    {


        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $contactPerson = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $contact = PersonContactFactory::new()->forPerson($person)->forContactPerson($contactPerson) ->create([
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
