<?php

namespace App\Tests\Api\Person;

use App\Factory\OrganizationFactory;
use App\Tests\ApiTestCase;
use App\Factory\PersonFactory;
use Doctrine\ORM\EntityManagerInterface;

final class PersonFilterTest extends ApiTestCase
{
    public function testFilterPeopleByFirstname(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $yves = PersonFactory::new()
            ->forOrganization($organization)
            ->create([
                'firstname' => 'Yves',
                'lastname' => 'Dupont',
                'email' => 'yves.dupont@example.com',
            ]);

        PersonFactory::new()
            ->forOrganization($organization)
            ->create([
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
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $yves = PersonFactory::new()
            ->forOrganization($organization)
            ->create([
                'firstname' => 'Yves',
                'lastname' => 'Dupont',
                'email' => 'yves.dupont@example.com',
            ]);

        $anne = PersonFactory::new()
            ->forOrganization($organization)
            ->create([
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
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $yves = PersonFactory::new()
            ->forOrganization($organization)
            ->create([
                'firstname' => 'Yves',
                'lastname' => 'Dupont',
                'email' => 'yves.dupont@example.com',
            ]);

        $anne = PersonFactory::new()
            ->forOrganization($organization)
            ->create([
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

        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $person1 = PersonFactory::new()
            ->forOrganization($organization)
            ->create([
                'firstname' => 'Yves',
                'lastname' => 'Dupont',
                'email' => 'yves.dupont@example.com',
            ]);

        $person2 = PersonFactory::new()
            ->forOrganization($organization)
            ->create([
                'firstname' => 'Anne',
                'lastname' => 'Martin',
                'email' => 'anne.martin@example.com',
            ]);

        PersonFactory::new()
            ->forOrganization($organization)
            ->create([
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


    public function testGetPersonsCanFilterByCreatedFromPublicRegistrationTrue(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $publicPerson = PersonFactory::new()
            ->forOrganization($organization)
            ->create([
                'firstname' => 'Public',
                'lastname' => 'Registration',
            ]);

        $publicPerson->markAsCreatedFromPublicRegistration();

        PersonFactory::new()
            ->forOrganization($organization)
            ->create([
                'firstname' => 'BackOffice',
                'lastname' => 'Managed',
            ]);

        static::getContainer()
            ->get(EntityManagerInterface::class)
            ->flush();

        $response = $this->apiGet('/api/v1/people?createdFromPublicRegistration=true');

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('pagination', $data);

        $items = $data['items'];

        $this->assertCount(1, $items);

        $this->assertSame($publicPerson->getPublicId(), $items[0]['id']);
        $this->assertSame('Public', $items[0]['firstname']);
        $this->assertSame('Registration', $items[0]['lastname']);
        $this->assertTrue($items[0]['createdFromPublicRegistration']);

        $this->assertSame(1, $data['pagination']['totalItems']);
    }

    public function testGetPersonsCanFilterByCreatedFromPublicRegistrationFalse(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        PersonFactory::new()
            ->forOrganization($organization)
            ->create([
                'firstname' => 'Public',
                'lastname' => 'Registration',
            ])
            ->markAsCreatedFromPublicRegistration();

        $managedPerson = PersonFactory::new()
            ->forOrganization($organization)
            ->create([
                'firstname' => 'BackOffice',
                'lastname' => 'Managed',
            ]);

        static::getContainer()
            ->get(EntityManagerInterface::class)
            ->flush();

        $response = $this->apiGet('/api/v1/people?createdFromPublicRegistration=false');

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('pagination', $data);

        $items = $data['items'];

        $this->assertCount(1, $items);

        $this->assertSame($managedPerson->getPublicId(), $items[0]['id']);
        $this->assertSame('BackOffice', $items[0]['firstname']);
        $this->assertSame('Managed', $items[0]['lastname']);
        $this->assertFalse($items[0]['createdFromPublicRegistration']);

        $this->assertSame(1, $data['pagination']['totalItems']);
    }


    public function testGetPersonFromAnotherOrganizationReturnsNotFound(): void
    {
        $this->getAuthenticatedOrganizationContext();

        $otherOrganization = OrganizationFactory::new()->create();

        $person = PersonFactory::new()
            ->forOrganization($otherOrganization)
            ->create();

        $response = $this->apiGet('/api/v1/people/' . $person->getPublicId());

        self::assertResponseStatusCodeSame(404);
    }

}
