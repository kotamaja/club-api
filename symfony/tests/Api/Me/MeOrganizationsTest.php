<?php

namespace App\Tests\Api\Me;

use App\Factory\OrganizationFactory;
use App\Factory\OrganizationUserFactory;
use App\Factory\PersonFactory;
use App\Tests\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;

final class MeOrganizationsTest extends ApiTestCase
{
    public function testGetMeOrganizationsReturnsOnlyOrganizationsAvailableToCurrentUser(): void
    {
        $context = $this->getAuthenticatedOrganizationContext();

        $connectionUser = $context->connectionUser;

        $organization = $context->organization;

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $organizationUser =  $context->organizationUser ;

        $organizationUser->linkPerson($person);

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->flush();


        $otherOrganization = OrganizationFactory::createOne([
            'name' => 'Other organization',
        ]);

        $otherPerson = PersonFactory::new()
            ->forOrganization($otherOrganization)
            ->create();


        OrganizationUserFactory::new()
            ->forConnectionUser($connectionUser)
            ->forOrganization($otherOrganization)
            ->forPerson($otherPerson)
            ->create();

        $notAvailableOrganization = OrganizationFactory::createOne([
            'name' => 'Not available organization',
        ]);

        $response = $this->apiGet('/api/v1/me/organizations');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        self::assertCount(2, $data);

        $organizationNames = array_column($data, 'organizationName');

        self::assertContains($context->organization->getName(), $organizationNames);
        self::assertContains('Other organization', $organizationNames);
        self::assertNotContains('Not available organization', $organizationNames);
    }
}
