<?php

namespace App\Tests\Api\PersonContact;

use App\Tests\ApiTestCase;
use App\Factory\PersonContactFactory;

final class PersonContactDeleteTest extends ApiTestCase
{
    public function testDelete(): void
    {
        $contact = PersonContactFactory::createOne();

        $this->apiDelete('/api/v1/person_contacts/'.$contact->getPublicId());

        $this->assertResponseStatusCodeSame(204);

        $this->apiGet('/api/v1/person_contacts/'.$contact->getPublicId());

        $this->assertResponseStatusCodeSame(404);
    }
}
