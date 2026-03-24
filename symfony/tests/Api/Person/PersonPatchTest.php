<?php

namespace App\Tests\Api\Person;

use App\Tests\ApiTestCase;
use App\Factory\PersonFactory;

final class PersonPatchTest extends ApiTestCase
{
    public function testPatch(): void
    {
        $person = PersonFactory::createOne([
            'firstname' => 'Yves',
            'lastname' => 'Dupont',
            'email' => 'yves.dupont@example.com',
        ]);

        $response = $this->apiPatch('/api/v1/people/' . $person->getPublicId(), [
            'lastname' => 'Durand',
        ]);

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertArrayHasValidUlid($data, 'id');

        $this->assertSame($person->getPublicId(), $data['id']);

        $this->assertJsonContains([
            'firstname' => 'Yves',
            'lastname' => 'Durand',
            'email' => 'yves.dupont@example.com',
        ]);
    }
}
