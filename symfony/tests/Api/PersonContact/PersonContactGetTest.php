<?php

namespace App\Tests\Api\PersonContact;

use App\Tests\ApiTestCase;
use App\Factory\PersonFactory;
use App\Factory\PersonContactFactory;

final class PersonContactGetTest extends ApiTestCase
{
    public function testGetCollection(): void
    {

        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $contactPerson = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $contact = PersonContactFactory::new()->forPerson($person)->forContactPerson($contactPerson) ->create();


        $response = $this->apiGet('/api/v1/person_contacts');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertSame(1, $data['pagination']['totalItems']);
        $this->assertCount(1, $data['items']);

        $item = $data['items'][0];

        $this->assertArrayHasValidUlid($item, 'id');
        $this->assertArrayHasValidUlid($item, 'personId');
        $this->assertArrayHasValidUlid($item, 'contactPersonId');

        $this->assertSame($contact->getPublicId(), $item['id']);
        $this->assertSame($person->getPublicId(), $item['personId']);
        $this->assertSame($contactPerson->getPublicId(), $item['contactPersonId']);
    }

    public function testGetItem(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $contactPerson = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $contact = PersonContactFactory::new()->forPerson($person)->forContactPerson($contactPerson) ->create();

        $response = $this->apiGet('/api/v1/person_contacts/'.$contact->getPublicId());

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertArrayHasValidUlid($data, 'id');
        $this->assertArrayHasValidUlid($data, 'personId');
        $this->assertArrayHasValidUlid($data, 'contactPersonId');

        $this->assertSame($contact->getPublicId(), $data['id']);
        $this->assertSame($person->getPublicId(), $data['personId']);
        $this->assertSame($contactPerson->getPublicId(), $data['contactPersonId']);
    }
}
