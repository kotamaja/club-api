<?php

namespace App\Tests\Api\PersonContact;

use App\Factory\PersonFactory;
use App\Tests\ApiTestCase;
use App\Factory\PersonContactFactory;

final class PersonContactDeleteTest extends ApiTestCase
{
    public function testDelete(): void
    {

        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $personContact = PersonFactory::new()
            ->forOrganization($organization)
            ->create();


        $contact = PersonContactFactory::new()->forPerson($person)->forContactPerson($personContact) ->create();

        $this->apiDelete('/api/v1/person_contacts/'.$contact->getPublicId());

        $this->assertResponseStatusCodeSame(204);

        $this->apiGet('/api/v1/person_contacts/'.$contact->getPublicId());

        $this->assertResponseStatusCodeSame(404);
    }
}
