<?php

namespace App\Tests\Api\EventRegistration;

use App\Core\Event\Enum\EventRegistrationStatus;
use App\Factory\EventFactory;
use App\Factory\EventRegistrationFactory;
use App\Factory\OrganizationFactory;
use App\Factory\PersonFactory;
use App\Tests\ApiTestCase;

final class EventRegistrationCancelTest extends ApiTestCase
{
    public function testCancelRegisteredEventRegistration(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create();

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create([
                'firstname' => 'Jean',
                'lastname' => 'Dupont',
            ]);

        $registration = EventRegistrationFactory::new()
            ->forEvent($event)
            ->forPerson($person)
            ->registered()
            ->create();

        $response = $this->apiPost('/api/v1/event-registrations/' . $registration->getPublicId() . '/cancel', []);

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertArrayHasValidUlid($data, 'id');

        $this->assertSame($registration->getPublicId(), $data['id']);
        $this->assertSame($event->getPublicId(), $data['eventId']);
        $this->assertSame($person->getPublicId(), $data['personId']);

        $this->assertSame('Jean', $data['personFirstname']);
        $this->assertSame('Dupont', $data['personLastname']);

        $this->assertSame(EventRegistrationStatus::Cancelled->value, $data['status']);
        $this->assertArrayHasKey('cancelledAt', $data);
        $this->assertNotNull($data['cancelledAt']);
    }

    public function testCancelWaitlistedEventRegistration(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create();

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $registration = EventRegistrationFactory::new()
            ->forEvent($event)
            ->forPerson($person)
            ->waitlisted()
            ->create();

        $response = $this->apiPost('/api/v1/event-registrations/' . $registration->getPublicId() . '/cancel', []);

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertSame($registration->getPublicId(), $data['id']);
        $this->assertSame($event->getPublicId(), $data['eventId']);
        $this->assertSame($person->getPublicId(), $data['personId']);
        $this->assertSame(EventRegistrationStatus::Cancelled->value, $data['status']);

        $this->assertArrayHasKey('cancelledAt', $data);
        $this->assertNotNull($data['cancelledAt']);
    }

    public function testCancelAlreadyCancelledEventRegistrationIsIdempotent(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create();

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $registration = EventRegistrationFactory::new()
            ->forEvent($event)
            ->forPerson($person)
            ->registered()
            ->create();

        $firstResponse = $this->apiPost('/api/v1/event-registrations/' . $registration->getPublicId() . '/cancel', []);

        $this->assertResponseStatusCodeSame(200);

        $firstData = $firstResponse->toArray();

        $this->assertSame(EventRegistrationStatus::Cancelled->value, $firstData['status']);
        $this->assertArrayHasKey('cancelledAt', $firstData);
        $this->assertNotNull($firstData['cancelledAt']);

        $secondResponse = $this->apiPost('/api/v1/event-registrations/' . $registration->getPublicId() . '/cancel', []);

        $this->assertResponseStatusCodeSame(200);

        $secondData = $secondResponse->toArray();

        $this->assertSame($registration->getPublicId(), $secondData['id']);
        $this->assertSame(EventRegistrationStatus::Cancelled->value, $secondData['status']);
        $this->assertSame($firstData['cancelledAt'], $secondData['cancelledAt']);
    }

    public function testCancelEventRegistrationFromAnotherOrganizationReturnsNotFound(): void
    {
        $this->getAuthenticatedOrganizationContext();

        $otherOrganization = OrganizationFactory::createOne();

        $event = EventFactory::new()
            ->forOrganization($otherOrganization)
            ->create();

        $person = PersonFactory::new()
            ->forOrganization($otherOrganization)
            ->create();

        $registration = EventRegistrationFactory::new()
            ->forEvent($event)
            ->forPerson($person)
            ->registered()
            ->create();

        $this->apiPost('/api/v1/event-registrations/' . $registration->getPublicId() . '/cancel', []);

        $this->assertResponseStatusCodeSame(404);
    }
}
