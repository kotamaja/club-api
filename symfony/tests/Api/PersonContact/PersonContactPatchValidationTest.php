<?php

namespace App\Tests\Api\PersonContact;

use App\Tests\ApiTestCase;
use App\Tests\Factory\PersonContactFactory;

final class PersonContactPatchValidationTest extends ApiTestCase
{
    public function testPatchReturns400WhenTypeIsInvalid(): void
    {
        $contact = PersonContactFactory::createOne();

        $this->apiPatch(
            '/api/v1/person_contacts/'.$contact->getPublicId(),
            [
                'type' => 'invalid_type',
            ]
        );

        $this->assertResponseStatusCodeSame(400);
    }
}
