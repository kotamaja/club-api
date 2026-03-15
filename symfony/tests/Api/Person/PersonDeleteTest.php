<?php

namespace App\Tests\Api\Person;

use App\Tests\ApiTestCase;
use App\Tests\Factory\PersonFactory;

class PersonDeleteTest  extends ApiTestCase
{
    public function testDelete(): void
    {
        $person = PersonFactory::createOne([
            'firstname' => 'Yves',
            'lastname' => 'Dupont',
            'email' => 'yves.dupont@example.com',
        ]);

        $this->apiDelete('/api/v1/people/' . $person->getPublicId());

        $this->assertResponseStatusCodeSame(204);

        $this->apiGet('/api/v1/people/' . $person->getPublicId());

        $this->assertResponseStatusCodeSame(404);
    }

}
