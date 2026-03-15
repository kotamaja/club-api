<?php

namespace App\Tests\Api\Person;

use App\Tests\ApiTestCase;
use App\Tests\Factory\PersonFactory;
use Symfony\Component\Uid\Ulid;

final class PersonGetTest extends ApiTestCase
{
    public function testGetCollection(): void
    {
        $people = PersonFactory::createMany(3);

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
        $person = PersonFactory::createOne([
            'firstname' => 'Yves',
            'lastname' => 'Dupont',
            'email' => 'yves.dupont@example.com',
        ]);

        $response = $this->apiGet('/api/v1/people/' . $person->getPublicId());

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertArrayHasValidUlid($data, 'id');
        $this->assertSame($person->getPublicId(), $data['id']);

        $this->assertJsonContains([
            'id' => $person->getPublicId(),
            'firstname' => 'Yves',
            'lastname' => 'Dupont',
            'email' => 'yves.dupont@example.com',
        ]);
    }


}
