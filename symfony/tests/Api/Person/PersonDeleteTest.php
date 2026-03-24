<?php

namespace App\Tests\Api\Person;

use App\Tests\ApiTestCase;
use App\Factory\MembershipFactory;
use App\Factory\PersonContactFactory;
use App\Factory\PersonFactory;

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

    public function testDeleteWithContactPerson(): void
    {
        $person = PersonFactory::createOne([
        ]);

        $parent = PersonFactory::createOne([
        ]);

        PersonContactFactory::createOne([
            'person' => $person,
            'contactPerson' => $parent]);


        $this->apiDelete('/api/v1/people/' . $person->getPublicId());

        $this->assertResponseStatusCodeSame(409);

        $this->apiDelete('/api/v1/people/' . $parent->getPublicId());

        $this->assertResponseStatusCodeSame(409);

    }

    public function testDeleteWithMembership(): void
    {
        $person = PersonFactory::createOne();

        MembershipFactory::createOne([
            'person' => $person,
        ]);


        $this->apiDelete('/api/v1/people/' . $person->getPublicId());

        $this->assertResponseStatusCodeSame(409);

    }


}
