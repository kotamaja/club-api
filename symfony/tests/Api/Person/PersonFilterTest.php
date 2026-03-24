<?php

namespace App\Tests\Api\Person;

use App\Tests\ApiTestCase;
use App\Factory\PersonFactory;

final class PersonFilterTest extends ApiTestCase
{
    public function testFilterPeopleByFirstname(): void
    {
        $yves = PersonFactory::createOne([
            'firstname' => 'Yves',
            'lastname' => 'Dupont',
            'email' => 'yves.dupont@example.com',
        ]);

        PersonFactory::createOne([
            'firstname' => 'Anne',
            'lastname' => 'Martin',
            'email' => 'anne.martin@example.com',
        ]);

        $response = $this->apiGet('/api/v1/people?firstname=Yves');

        $this->assertResponseIsSuccessful();

        $item = $this->assertSingleResult($response->toArray());

        $this->assertSame('Yves', $item['firstname']);
        $this->assertArrayHasValidUlid($item, 'id');
        $this->assertSame($yves->getPublicId(), $item['id']);
    }


    public function testFilterPeopleByLastname(): void
    {
        PersonFactory::createOne([
            'firstname' => 'Yves',
            'lastname' => 'Dupont',
            'email' => 'yves.dupont@example.com',
        ]);

        $anne = PersonFactory::createOne([
            'firstname' => 'Anne',
            'lastname' => 'Martin',
            'email' => 'anne.martin@example.com',
        ]);

        $response = $this->apiGet('/api/v1/people?lastname=Martin');

        $this->assertResponseIsSuccessful();

        $item = $this->assertSingleResult($response->toArray());

        $this->assertSame('Martin', $item['lastname']);
        $this->assertArrayHasValidUlid($item, 'id');
        $this->assertSame($anne->getPublicId(), $item['id']);
    }

    public function testFilterPeopleByEmail(): void
    {
        PersonFactory::createOne([
            'firstname' => 'Yves',
            'lastname' => 'Dupont',
            'email' => 'yves.dupont@example.com',
        ]);

        $anne = PersonFactory::createOne([
            'firstname' => 'Anne',
            'lastname' => 'Martin',
            'email' => 'anne.martin@example.com',
        ]);

        $response = $this->apiGet('/api/v1/people?email=anne.martin');

        $this->assertResponseIsSuccessful();


        $item = $this->assertSingleResult($response->toArray());

        $this->assertSame('anne.martin@example.com', $item['email']);
        $this->assertArrayHasValidUlid($item, 'id');
        $this->assertSame($anne->getPublicId(), $item['id']);
    }

    public function testFilterPeopleByIds(): void
    {
        $person1 = PersonFactory::createOne([
            'firstname' => 'Yves',
            'lastname' => 'Dupont',
            'email' => 'yves.dupont@example.com',
        ]);

        $person2 = PersonFactory::createOne([
            'firstname' => 'Anne',
            'lastname' => 'Martin',
            'email' => 'anne.martin@example.com',
        ]);

        PersonFactory::createOne([
            'firstname' => 'Paul',
            'lastname' => 'Durand',
            'email' => 'paul.durand@example.com',
        ]);

        $response = $this->apiGet('/api/v1/people?id[]=' . $person1->getPublicId() . '&id[]=' . $person2->getPublicId());

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertSame(2, $data['pagination']['totalItems']);

        foreach ($data['items'] as $item) {
            $this->assertArrayHasValidUlid($item, 'id');
        }

        $returnedIds = array_map(fn(array $item) => $item['id'], $data['items']);

        $this->assertEqualsCanonicalizing(
            [$person1->getPublicId(), $person2->getPublicId()],
            $returnedIds
        );
    }

    private function assertSingleResult(array $data): array
    {
        $this->assertSame(1, $data['pagination']['totalItems']);
        $this->assertCount(1, $data['items']);

        return $data['items'][0];
    }
}
