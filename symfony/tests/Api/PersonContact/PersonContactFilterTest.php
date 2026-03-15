<?php

namespace App\Tests\Api\PersonContact;

use App\Enum\RelationshipType;
use App\Tests\ApiTestCase;
use App\Tests\Factory\PersonContactFactory;
use App\Tests\Factory\PersonFactory;

final class PersonContactFilterTest extends ApiTestCase
{
    public function testFilterByPersonId(): void
    {
        $person = PersonFactory::createOne();
        $otherPerson = PersonFactory::createOne();

        $contactPerson1 = PersonFactory::createOne();
        $contactPerson2 = PersonFactory::createOne();

        $contact1 = PersonContactFactory::createOne([
            'person' => $person,
            'contactPerson' => $contactPerson1,
            'type' => RelationshipType::PARENT,
        ]);

        PersonContactFactory::createOne([
            'person' => $otherPerson,
            'contactPerson' => $contactPerson2,
            'type' => RelationshipType::LEGAL_GUARDIAN,
        ]);

        $response = $this->apiGet('/api/v1/person_contacts?personId[]='.$person->getPublicId());

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertSame(1, $data['pagination']['totalItems']);
        $this->assertCount(1, $data['items']);

        $item = $data['items'][0];

        $this->assertArrayHasValidUlid($item, 'id');
        $this->assertSame($contact1->getPublicId(), $item['id']);
        $this->assertSame($person->getPublicId(), $item['personId']);
    }

    public function testFilterByContactPersonId(): void
    {
        $person1 = PersonFactory::createOne();
        $person2 = PersonFactory::createOne();

        $contactPerson = PersonFactory::createOne();
        $otherContactPerson = PersonFactory::createOne();

        PersonContactFactory::createOne([
            'person' => $person1,
            'contactPerson' => $otherContactPerson,
            'type' => RelationshipType::PARENT,
        ]);

        $contact = PersonContactFactory::createOne([
            'person' => $person2,
            'contactPerson' => $contactPerson,
            'type' => RelationshipType::LEGAL_GUARDIAN,
        ]);

        $response = $this->apiGet('/api/v1/person_contacts?contactPersonId[]='.$contactPerson->getPublicId());

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertSame(1, $data['pagination']['totalItems']);
        $this->assertCount(1, $data['items']);

        $item = $data['items'][0];

        $this->assertArrayHasValidUlid($item, 'id');
        $this->assertSame($contact->getPublicId(), $item['id']);
        $this->assertSame($contactPerson->getPublicId(), $item['contactPersonId']);
    }

    public function testFilterByType(): void
    {
        $person1 = PersonFactory::createOne();
        $person2 = PersonFactory::createOne();
        $person3 = PersonFactory::createOne();
        $person4 = PersonFactory::createOne();

        $contact = PersonContactFactory::createOne([
            'person' => $person1,
            'contactPerson' => $person2,
            'type' => RelationshipType::PARENT,
        ]);

        PersonContactFactory::createOne([
            'person' => $person3,
            'contactPerson' => $person4,
            'type' => RelationshipType::LEGAL_GUARDIAN,
        ]);

        $response = $this->apiGet('/api/v1/person_contacts?type=parent');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertSame(1, $data['pagination']['totalItems']);
        $this->assertCount(1, $data['items']);

        $item = $data['items'][0];

        $this->assertArrayHasValidUlid($item, 'id');
        $this->assertSame($contact->getPublicId(), $item['id']);
        $this->assertSame('parent', $item['type']);
    }

    public function testFilterByEmergencyContact(): void
    {
        $person1 = PersonFactory::createOne();
        $person2 = PersonFactory::createOne();
        $person3 = PersonFactory::createOne();
        $person4 = PersonFactory::createOne();

        $contact = PersonContactFactory::createOne([
            'person' => $person1,
            'contactPerson' => $person2,
            'type' => RelationshipType::PARENT,
            'isEmergencyContact' => true,
        ]);

        PersonContactFactory::createOne([
            'person' => $person3,
            'contactPerson' => $person4,
            'type' => RelationshipType::LEGAL_GUARDIAN,
            'isEmergencyContact' => false,
        ]);

        $response = $this->apiGet('/api/v1/person_contacts?isEmergencyContact=true');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertSame(1, $data['pagination']['totalItems']);
        $this->assertCount(1, $data['items']);

        $item = $data['items'][0];

        $this->assertArrayHasValidUlid($item, 'id');
        $this->assertSame($contact->getPublicId(), $item['id']);
        $this->assertTrue($item['isEmergencyContact']);
    }
}
