<?php

namespace App\Tests\Api\Event;

use App\Core\Event\Enum\EventRegistrationStatus;
use App\Factory\EventFactory;
use App\Factory\EventRegistrationFactory;
use App\Factory\OrganizationFactory;
use App\Factory\PersonFactory;
use App\Tests\ApiTestCase;

final class EventDeleteTest extends ApiTestCase
{
    public function testDeleteEventWithoutRegistrations(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create();

        $this->apiDelete('/api/v1/events/' . $event->getPublicId());

        $this->assertResponseStatusCodeSame(204);

        $this->apiGet('/api/v1/events/' . $event->getPublicId());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteRejectsEventWithRegistration(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create();

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        EventRegistrationFactory::new()
            ->forEvent($event)
            ->forPerson($person)
            ->registered()
            ->create();

        $this->apiDelete('/api/v1/events/' . $event->getPublicId());

        $this->assertResponseStatusCodeSame(422);

        $this->apiGet('/api/v1/events/' . $event->getPublicId());

        $this->assertResponseStatusCodeSame(200);
    }

    public function testDeleteEventFromAnotherOrganizationReturnsNotFound(): void
    {
        $this->getAuthenticatedOrganizationContext();

        $otherOrganization = OrganizationFactory::createOne();

        $event = EventFactory::new()
            ->forOrganization($otherOrganization)
            ->create();

        $this->apiDelete('/api/v1/events/' . $event->getPublicId());

        $this->assertResponseStatusCodeSame(404);
    }
}
