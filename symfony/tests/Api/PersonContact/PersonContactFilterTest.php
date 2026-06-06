<?php

namespace App\Tests\Api\PersonContact;

use App\Enum\RelationshipType;
use App\Factory\PersonContactFactory;
use App\Factory\PersonFactory;
use App\Tests\ApiTestCase;

final class PersonContactFilterTest extends ApiTestCase
{
    public function testFilterByPersonId(): void
    {

        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $otherPerson = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $contactPerson1 = PersonFactory::new()
            ->forOrganization($organization)
            ->create();
        $contactPerson2 = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $contact1 = PersonContactFactory::new()->forPerson($person)->forContactPerson($contactPerson1)->create(['type' => RelationshipType::PARENT,]);
        PersonContactFactory::new()->forPerson($otherPerson)->forContactPerson($contactPerson2)->create(['type' => RelationshipType::LEGAL_GUARDIAN,]);


        $response = $this->apiGet('/api/v1/person_contacts?personId[]=' . $person->getPublicId());

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

        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person1 = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $person2 = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $contactPerson = PersonFactory::new()
            ->forOrganization($organization)
            ->create();
        $otherContactPerson = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        PersonContactFactory::new()->forPerson($person1)->forContactPerson($otherContactPerson)->create(['type' => RelationshipType::PARENT,]);
        $contact = PersonContactFactory::new()->forPerson($person2)->forContactPerson($contactPerson)->create(['type' => RelationshipType::LEGAL_GUARDIAN,]);


        $response = $this->apiGet('/api/v1/person_contacts?contactPersonId[]=' . $contactPerson->getPublicId());

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

        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person1 = PersonFactory::new()
            ->forOrganization($organization)
            ->create();
        $person2 = PersonFactory::new()
            ->forOrganization($organization)
            ->create();
        $person3 = PersonFactory::new()
            ->forOrganization($organization)
            ->create();
        $person4 = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $contact = PersonContactFactory::new()->forPerson($person1)->forContactPerson($person2)->create(['type' => RelationshipType::PARENT,]);
        PersonContactFactory::new()->forPerson($person3)->forContactPerson($person4)->create(['type' => RelationshipType::LEGAL_GUARDIAN,]);

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

        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person1 = PersonFactory::new()
            ->forOrganization($organization)
            ->create();
        $person2 = PersonFactory::new()
            ->forOrganization($organization)
            ->create();
        $person3 = PersonFactory::new()
            ->forOrganization($organization)
            ->create();
        $person4 = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $contact = PersonContactFactory::new()->forPerson($person1)->forContactPerson($person2)->create(['type' => RelationshipType::PARENT, 'isEmergencyContact' => true,]);
        PersonContactFactory::new()->forPerson($person3)->forContactPerson($person4)->create(['type' => RelationshipType::LEGAL_GUARDIAN, 'isEmergencyContact' => false,]);

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
