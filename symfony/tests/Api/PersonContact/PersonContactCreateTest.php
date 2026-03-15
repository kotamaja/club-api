<?php

namespace App\Tests\Api\PersonContact;

use App\Tests\ApiTestCase;
use App\Tests\Factory\PersonFactory;

final class PersonContactCreateTest extends ApiTestCase
{
    public function testCreate(): void
    {
        $person = PersonFactory::createOne([
            'firstname' => 'Tom',
            'lastname' => 'Junior',
            'email' => 'tom.junior@example.com',
        ]);

        $contactPerson = PersonFactory::createOne([
            'firstname' => 'Marie',
            'lastname' => 'Parent',
            'email' => 'marie.parent@example.com',
        ]);

        $response = $this->apiPost('/api/v1/person_contacts', [
            'personId' => $person->getPublicId(),
            'contactPersonId' => $contactPerson->getPublicId(),
            'type' => 'parent',
            'isEmergencyContact' => true,
        ]);

        $this->assertResponseStatusCodeSame(201);

        $data = $response->toArray();

        $this->assertArrayHasValidUlid($data, 'id');
        $this->assertArrayHasValidUlid($data, 'personId');
        $this->assertArrayHasValidUlid($data, 'contactPersonId');

        $this->assertSame($person->getPublicId(), $data['personId']);
        $this->assertSame($contactPerson->getPublicId(), $data['contactPersonId']);
        $this->assertSame('parent', $data['type']);
        $this->assertTrue($data['isEmergencyContact']);
    }
}
