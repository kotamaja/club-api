<?php

namespace App\Tests\Api\PersonContact;

use App\Tests\ApiTestCase;
use App\Tests\Factory\PersonFactory;

final class PersonContactCreateValidationTest extends ApiTestCase
{
    public function testCreateRejectsInvalidPersonId(): void
    {
        $contactPerson = PersonFactory::createOne();

        $this->apiPost('/api/v1/person_contacts', [
            'personId' => 'not-a-valid-ulid',
            'contactPersonId' => $contactPerson->getPublicId(),
            'type' => 'parent',
            'isEmergencyContact' => true,
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateRejectsInvalidContactPersonId(): void
    {
        $person = PersonFactory::createOne();

        $this->apiPost('/api/v1/person_contacts', [
            'personId' => $person->getPublicId(),
            'contactPersonId' => 'not-a-valid-ulid',
            'type' => 'parent',
            'isEmergencyContact' => true,
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateRejectsSamePersonAndContactPerson(): void
    {
        $person = PersonFactory::createOne();

        $this->apiPost('/api/v1/person_contacts', [
            'personId' => $person->getPublicId(),
            'contactPersonId' => $person->getPublicId(),
            'type' => 'parent',
            'isEmergencyContact' => true,
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateRejectsDuplicateRelation(): void
    {
        $person = PersonFactory::createOne();
        $contactPerson = PersonFactory::createOne();

        $this->apiPost('/api/v1/person_contacts', [
            'personId' => $person->getPublicId(),
            'contactPersonId' => $contactPerson->getPublicId(),
            'type' => 'parent',
            'isEmergencyContact' => true,
        ]);

        $this->assertResponseStatusCodeSame(201);

        $this->apiPost('/api/v1/person_contacts', [
            'personId' => $person->getPublicId(),
            'contactPersonId' => $contactPerson->getPublicId(),
            'type' => 'parent',
            'isEmergencyContact' => false,
        ]);

        $this->assertResponseStatusCodeSame(409);
    }
}
