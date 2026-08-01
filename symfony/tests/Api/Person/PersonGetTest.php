<?php

namespace App\Tests\Api\Person;

use App\Factory\PersonFactory;
use App\Tests\ApiTestCase;
use Symfony\Component\Uid\Ulid;

final class PersonGetTest extends ApiTestCase
{
    public function testGetCollection(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;


        $people = PersonFactory::new()
            ->forOrganization($organization)->many(3)
            ->create();


        $expectedIds = array_map(
            fn($p) => $p->getPublicId(),
            $people
        );

        $response = $this->apiGet('/api/v1/people');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertSame(3, $data['pagination']['totalItems']);
        $this->assertCount(3, $data['items']);

        $returnedIds = array_map(
            fn($item) => $item['id'],
            $data['items']
        );

        foreach ($returnedIds as $id) {
            $this->assertTrue(Ulid::isValid($id));
        }

        $this->assertEqualsCanonicalizing($expectedIds, $returnedIds);
    }

    public function testGetItem(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;


        $person = PersonFactory::new()->forOrganization($organization)->create();




        $response = $this->apiGet('/api/v1/people/' . $person->getPublicId());

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertArrayHasValidUlid($data, 'id');
        $this->assertSame($person->getPublicId(), $data['id']);

        $this->assertJsonContains([
            'id' => $person->getPublicId(),
            'firstname' => $person->getFirstname(),
            'lastname' =>$person->getLastname(),
            'email' => $person->getEmail(),
        ]);

        $this->assertFalse($data['createdFromPublicRegistration']);
    }


}
