<?php

namespace App\Tests\Api\Me;

use App\Core\Enum\Feature;
use App\Core\Enum\Limit;
use App\Core\Enum\ServicePlan;
use App\Tests\ApiTestCase;

final class MeCurrentContextTest extends ApiTestCase
{
    public function testGetMeCurrentContextReturnsCapabilitiesForCurrentOrganization(): void
    {
        $context = $this->getAuthenticatedOrganizationContext(includePerson: true);

        $response = $this->apiGet('/api/v1/me/current-context');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        self::assertArrayHasKey('organization', $data);
        self::assertArrayHasKey('organizationUser', $data);
        self::assertArrayHasKey('person', $data);
        self::assertArrayHasKey('capabilities', $data);

        self::assertSame(
            $context->organization->getPublicId(),
            $data['organization']['id'],
        );

        self::assertSame(
            ServicePlan::Community->value,
            $data['capabilities']['servicePlan'],
        );

        self::assertTrue(
            $data['capabilities']['features'][Feature::EventBasic->value],
        );

        self::assertFalse(
            $data['capabilities']['features'][Feature::EventCustomForm->value],
        );

        self::assertSame(
            10,
            $data['capabilities']['limits'][Limit::MaxActiveEvents->value],
        );
    }

    public function testGetMeCurrentContextUsesRequestedOrganization(): void
    {

        $context = $this->getAuthenticatedOrganizationContext(includePerson: true, servicePlan: ServicePlan::Pro);

        $organization = $context->organization;

        $response = $this->apiGet(
            '/api/v1/me/current-context',
        );
//
        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        self::assertSame(
            $organization->getPublicId(),
            $data['organization']['id'],
        );

        self::assertSame(
            ServicePlan::Pro->value,
            $data['capabilities']['servicePlan'],
        );

        self::assertTrue(
            $data['capabilities']['features'][Feature::EventBasic->value],
        );

        self::assertTrue(
            $data['capabilities']['features'][Feature::EventCustomForm->value],
        );

        self::assertNull(
            $data['capabilities']['limits'][Limit::MaxActiveEvents->value],
        );
    }
}
