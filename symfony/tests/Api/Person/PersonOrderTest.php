<?php

namespace App\Tests\Api\Person;

use App\Tests\ApiTestCase;
use App\Factory\PersonFactory;

final class PersonOrderTest extends ApiTestCase
{
    public function testOrderPeopleByLastnameAscending(): void
    {
        $zulu = PersonFactory::createOne([
            'firstname' => 'Yves',
            'lastname' => 'Zulu',
            'email' => 'yves.zulu@example.com',
        ]);

        $alpha = PersonFactory::createOne([
            'firstname' => 'Anne',
            'lastname' => 'Alpha',
            'email' => 'anne.alpha@example.com',
        ]);

        $response = $this->apiGet('/api/v1/people?orderLastname=asc');

        $this->assertResponseIsSuccessful();

        $items = $this->assertTwoResults($response->toArray());

        $this->assertSame('Alpha', $items[0]['lastname']);
        $this->assertSame('Zulu', $items[1]['lastname']);

        $this->assertArrayHasValidUlid($items[0], 'id');
        $this->assertSame($alpha->getPublicId(), $items[0]['id']);

        $this->assertArrayHasValidUlid($items[1], 'id');
        $this->assertSame($zulu->getPublicId(), $items[1]['id']);

    }

    public function testOrderPeopleByFirstnameDescending(): void
    {
        $anne = PersonFactory::createOne([
            'firstname' => 'Anne',
            'lastname' => 'Martin',
            'email' => 'anne.martin@example.com',
        ]);

        $yves = PersonFactory::createOne([
            'firstname' => 'Yves',
            'lastname' => 'Dupont',
            'email' => 'yves.dupont@example.com',
        ]);

        $response = $this->apiGet('/api/v1/people?orderFirstname=desc');

        $this->assertResponseIsSuccessful();


        $items = $this->assertTwoResults($response->toArray());

        $this->assertSame('Yves', $items[0]['firstname']);
        $this->assertSame('Anne', $items[1]['firstname']);

        $this->assertArrayHasValidUlid($items[0], 'id');
        $this->assertSame($yves->getPublicId(), $items[0]['id']);

        $this->assertArrayHasValidUlid($items[1], 'id');
        $this->assertSame($anne->getPublicId(), $items[1]['id']);
    }


    public function testOrderPeopleByEmailAscending(): void
    {
        $zeta= PersonFactory::createOne([
            'firstname' => 'Yves',
            'lastname' => 'Dupont',
            'email' => 'zeta@example.com',
        ]);

        $alpha = PersonFactory::createOne([
            'firstname' => 'Anne',
            'lastname' => 'Martin',
            'email' => 'alpha@example.com',
        ]);

        $response = $this->apiGet('/api/v1/people?orderEmail=asc');

        $this->assertResponseIsSuccessful();

        $items = $this->assertTwoResults($response->toArray());

        $this->assertSame('alpha@example.com', $items[0]['email']);
        $this->assertSame('zeta@example.com', $items[1]['email']);

        $this->assertArrayHasValidUlid($items[0], 'id');
        $this->assertSame($alpha->getPublicId(), $items[0]['id']);

        $this->assertArrayHasValidUlid($items[1], 'id');
        $this->assertSame($zeta->getPublicId(), $items[1]['id']);
    }


    private function assertTwoResults(array $data): array
    {
        $this->assertSame(2, $data['pagination']['totalItems']);
        $this->assertCount(2, $data['items']);

        return $data['items'];
    }

}
