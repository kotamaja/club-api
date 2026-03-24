<?php

namespace App\Tests\Api\PersonContact;

use App\Enum\RelationshipType;
use App\Tests\ApiTestCase;
use App\Factory\PersonContactFactory;
use App\Factory\PersonFactory;

final class PersonContactOrderTest extends ApiTestCase
{
    public function testOrderByTypeAscending(): void
    {
        $person1 = PersonFactory::createOne();
        $contactPerson1 = PersonFactory::createOne();

        $person2 = PersonFactory::createOne();
        $contactPerson2 = PersonFactory::createOne();

        $legalGuardian = PersonContactFactory::createOne([
            'person' => $person1,
            'contactPerson' => $contactPerson1,
            'type' => RelationshipType::LEGAL_GUARDIAN,
        ]);

        $parent = PersonContactFactory::createOne([
            'person' => $person2,
            'contactPerson' => $contactPerson2,
            'type' => RelationshipType::PARENT,
        ]);

        $response = $this->apiGet('/api/v1/person_contacts?orderType=asc');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertSame(2, $data['pagination']['totalItems']);
        $this->assertCount(2, $data['items']);

        $items = $data['items'];

        $this->assertSame('legal_guardian', $items[0]['type']);
        $this->assertSame('parent', $items[1]['type']);

        $this->assertArrayHasValidUlid($items[0], 'id');
        $this->assertSame($legalGuardian->getPublicId(), $items[0]['id']);

        $this->assertArrayHasValidUlid($items[1], 'id');
        $this->assertSame($parent->getPublicId(), $items[1]['id']);
    }

    public function testOrderByPersonIdAscending(): void
    {
        $personA = PersonFactory::createOne();
        $personB = PersonFactory::createOne();

        $contactPerson1 = PersonFactory::createOne();
        $contactPerson2 = PersonFactory::createOne();

        $contactA = PersonContactFactory::createOne([
            'person' => $personA,
            'contactPerson' => $contactPerson1,
            'type' => RelationshipType::PARENT,
        ]);

        $contactB = PersonContactFactory::createOne([
            'person' => $personB,
            'contactPerson' => $contactPerson2,
            'type' => RelationshipType::LEGAL_GUARDIAN,
        ]);

        $expected = [
            $contactA,
            $contactB,
        ];

        usort($expected, static fn($left, $right) => $left->getPerson()->getPublicId() <=> $right->getPerson()->getPublicId());

        $response = $this->apiGet('/api/v1/person_contacts?orderPersonId=asc');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertSame(2, $data['pagination']['totalItems']);
        $this->assertCount(2, $data['items']);

        $items = $data['items'];

        $this->assertArrayHasValidUlid($items[0], 'id');
        $this->assertSame($expected[0]->getPublicId(), $items[0]['id']);
        $this->assertSame($expected[0]->getPerson()->getPublicId(), $items[0]['personId']);

        $this->assertArrayHasValidUlid($items[1], 'id');
        $this->assertSame($expected[1]->getPublicId(), $items[1]['id']);
        $this->assertSame($expected[1]->getPerson()->getPublicId(), $items[1]['personId']);
    }

    public function testOrderByContactPersonIdAscending(): void
    {
        $person1 = PersonFactory::createOne();
        $person2 = PersonFactory::createOne();

        $contactPersonA = PersonFactory::createOne();
        $contactPersonB = PersonFactory::createOne();

        $contactA = PersonContactFactory::createOne([
            'person' => $person1,
            'contactPerson' => $contactPersonA,
            'type' => RelationshipType::PARENT,
        ]);

        $contactB = PersonContactFactory::createOne([
            'person' => $person2,
            'contactPerson' => $contactPersonB,
            'type' => RelationshipType::LEGAL_GUARDIAN,
        ]);

        $expected = [
            $contactA,
            $contactB,
        ];

        usort($expected, static fn($left, $right) => $left->getContactPerson()->getPublicId() <=> $right->getContactPerson()->getPublicId());

        $response = $this->apiGet('/api/v1/person_contacts?orderContactPersonId=asc');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        $this->assertSame(2, $data['pagination']['totalItems']);
        $this->assertCount(2, $data['items']);

        $items = $data['items'];

        $this->assertArrayHasValidUlid($items[0], 'id');
        $this->assertSame($expected[0]->getPublicId(), $items[0]['id']);
        $this->assertSame($expected[0]->getContactPerson()->getPublicId(), $items[0]['contactPersonId']);

        $this->assertArrayHasValidUlid($items[1], 'id');
        $this->assertSame($expected[1]->getPublicId(), $items[1]['id']);
        $this->assertSame($expected[1]->getContactPerson()->getPublicId(), $items[1]['contactPersonId']);
    }
}
