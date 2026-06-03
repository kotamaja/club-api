<?php

namespace App\Tests\Api\Person;

use App\Factory\MembershipFactory;
use App\Factory\PersonContactFactory;
use App\Factory\PersonFactory;
use App\Tests\ApiTestCase;

class PersonDeleteTest extends ApiTestCase
{
    public function testDelete(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create([
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
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person = PersonFactory::new()->forOrganization($organization)->create();

        $parent = PersonFactory::new()->forOrganization($organization)->create();

        PersonContactFactory::createOne([
            'person' => $person,
            'contactPerson' => $parent]);


        $this->apiDelete('/api/v1/people/' . $person->getPublicId());

        $this->assertResponseStatusCodeSame(422);

        $this->apiDelete('/api/v1/people/' . $parent->getPublicId());

        $this->assertResponseStatusCodeSame(422);

    }

    public function testDeleteWithMembership(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person = PersonFactory::new()->forOrganization($organization)->create();

        $membership = MembershipFactory::new()
            ->forPersonWithGeneratedClub($person)
            ->create();



        $this->apiDelete('/api/v1/people/' . $person->getPublicId());

        $this->assertResponseStatusCodeSame(422);

    }


}
