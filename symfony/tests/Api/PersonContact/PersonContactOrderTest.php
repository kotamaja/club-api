<?php

namespace App\Tests\Api\PersonContact;

use App\Enum\RelationshipType;
use App\Factory\PersonContactFactory;
use App\Factory\PersonFactory;
use App\Tests\ApiTestCase;

final class PersonContactOrderTest extends ApiTestCase
{
    public function testOrderByTypeAscending(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person1 = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $contactPerson1 = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $person2 = PersonFactory::new()
            ->forOrganization($organization)
            ->create();
        $contactPerson2 = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $legalGuardian = PersonContactFactory::new()->forPerson($person1)->forContactPerson($contactPerson1)->create(['type' => RelationshipType::LEGAL_GUARDIAN,]);
        $parent = PersonContactFactory::new()->forPerson($person2)->forContactPerson($contactPerson2)->create(['type' => RelationshipType::PARENT,]);

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

        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $personA = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $personB = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $contactPerson1 = PersonFactory::new()
            ->forOrganization($organization)
            ->create();
        $contactPerson2 = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $contactA = PersonContactFactory::new()->forPerson($personA)->forContactPerson($contactPerson1)->create(['type' => RelationshipType::PARENT,]);
        $contactB = PersonContactFactory::new()->forPerson($personB)->forContactPerson($contactPerson2)->create(['type' => RelationshipType::LEGAL_GUARDIAN,]);


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


        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $personA = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $personB = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $contactPersonA = PersonFactory::new()
            ->forOrganization($organization)
            ->create();
        $contactPersonB = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $contactA = PersonContactFactory::new()->forPerson($personA)->forContactPerson($contactPersonA)->create(['type' => RelationshipType::PARENT,]);
        $contactB = PersonContactFactory::new()->forPerson($personB)->forContactPerson($contactPersonB)->create(['type' => RelationshipType::LEGAL_GUARDIAN,]);


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
