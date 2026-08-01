<?php

namespace App\Tests\Api\Event;

use App\Core\Event\Enum\EventRegistrationStatus;
use App\Factory\EventFactory;
use App\Factory\EventRegistrationFactory;
use App\Factory\PersonFactory;
use App\Tests\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;

final class EventMyRegistrationStatusTest extends ApiTestCase
{
    public function testGetEventItemReturnsNullWhenCurrentPersonHasNoActiveRegistration(): void
    {
        $context = $this->getAuthenticatedOrganizationContext();
        $organization = $context->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create();

        $response = $this->apiGet('/api/v1/events/' . $event->getPublicId());

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertSame($event->getPublicId(), $data['id']);
        $this->assertNull($data['myRegistrationStatus'] ?? null);
    }

    public function testGetEventItemReturnsRegisteredForCurrentPerson(): void
    {
        $context = $this->getAuthenticatedOrganizationContext(includePerson: true);
        $organization = $context->organization;
        $person = $context->person;

        $this->assertNotNull($person);

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create();

        EventRegistrationFactory::new()
            ->forEvent($event)
            ->forPerson($person)
            ->registered()
            ->create();

        $response = $this->apiGet('/api/v1/events/' . $event->getPublicId());

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertSame($event->getPublicId(), $data['id']);
        $this->assertSame(EventRegistrationStatus::Registered->value, $data['myRegistrationStatus']);
    }

    public function testGetEventItemReturnsWaitlistedForCurrentPerson(): void
    {
        $context = $this->getAuthenticatedOrganizationContext(includePerson: true);
        $organization = $context->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create();

        EventRegistrationFactory::new()
            ->forEvent($event)
            ->forPerson($context->person)
            ->waitlisted()
            ->create();

        $response = $this->apiGet('/api/v1/events/' . $event->getPublicId());

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertSame($event->getPublicId(), $data['id']);
        $this->assertSame(EventRegistrationStatus::Waitlisted->value, $data['myRegistrationStatus']);
    }

    public function testGetEventItemIgnoresCancelledRegistrationForCurrentPerson(): void
    {
        $context = $this->getAuthenticatedOrganizationContext(includePerson: true);
        $organization = $context->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create();

        $registration = EventRegistrationFactory::new()
            ->forEvent($event)
            ->forPerson($context->person)
            ->registered()
            ->create();

        $registration->cancel(new \DateTimeImmutable('2026-09-01T10:00:00+02:00'));

        static::getContainer()
            ->get(id: EntityManagerInterface::class)
            ->flush();

        $response = $this->apiGet('/api/v1/events/' . $event->getPublicId());

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertSame($event->getPublicId(), $data['id']);
        $this->assertNull($data['myRegistrationStatus'] ?? null);
    }

    public function testGetEventItemIgnoresRegistrationOfAnotherPerson(): void
    {
        $context = $this->getAuthenticatedOrganizationContext(includePerson: true);
        $organization = $context->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create();

        $otherPerson = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        EventRegistrationFactory::new()
            ->forEvent($event)
            ->forPerson($otherPerson)
            ->registered()
            ->create();

        $response = $this->apiGet('/api/v1/events/' . $event->getPublicId());

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertSame($event->getPublicId(), $data['id']);
        $this->assertNull($data['myRegistrationStatus'] ?? null);
    }

    public function testGetEventCollectionReturnsMyRegistrationStatus(): void
    {
        $context = $this->getAuthenticatedOrganizationContext(includePerson: true);
        $organization = $context->organization;

        $registeredEvent = EventFactory::new()
            ->forOrganization($organization)
            ->create([
                'title' => 'Event inscrit',
            ]);

        $notRegisteredEvent = EventFactory::new()
            ->forOrganization($organization)
            ->create([
                'title' => 'Event non inscrit',
            ]);

        EventRegistrationFactory::new()
            ->forEvent($registeredEvent)
            ->forPerson($context->person)
            ->registered()
            ->create();

        $response = $this->apiGet('/api/v1/events');

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertArrayHasKey('items', $data);

        $itemsById = [];
        foreach ($data['items'] as $item) {
            $itemsById[$item['id']] = $item;
        }

        $this->assertArrayHasKey($registeredEvent->getPublicId(), $itemsById);
        $this->assertArrayHasKey($notRegisteredEvent->getPublicId(), $itemsById);

        $this->assertSame(
            EventRegistrationStatus::Registered->value,
            $itemsById[$registeredEvent->getPublicId()]['myRegistrationStatus'],
        );

        $this->assertNull($itemsById[$notRegisteredEvent->getPublicId()]['myRegistrationStatus'] ?? null);
    }

    public function testMyRegistrationStatusFollowsCreateAndCancelRegistrationApiFlow(): void
    {
        $context = $this->getAuthenticatedOrganizationContext(includePerson: true);
        $organization = $context->organization;
        $person = $context->person;

        $this->assertNotNull($person);

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create([
                'capacity' => 10,
                'waitlistEnabled' => true,
            ]);

        $event->publish();

        static::getContainer()
            ->get(EntityManagerInterface::class)
            ->flush();

        $response = $this->apiGet('/api/v1/events/' . $event->getPublicId());

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertSame($event->getPublicId(), $data['id']);
        $this->assertNull($data['myRegistrationStatus'] ?? null);

        $response = $this->apiPost('/api/v1/events/' . $event->getPublicId() . '/registrations', [
            'personId' => $person->getPublicId(),
        ]);

        $this->assertResponseStatusCodeSame(201);

        $registrationData = $response->toArray();

        $this->assertArrayHasValidUlid($registrationData, 'id');
        $this->assertSame($event->getPublicId(), $registrationData['eventId']);
        $this->assertSame($person->getPublicId(), $registrationData['personId']);
        $this->assertSame(EventRegistrationStatus::Registered->value, $registrationData['status']);

        $response = $this->apiGet('/api/v1/events/' . $event->getPublicId());

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertSame($event->getPublicId(), $data['id']);
        $this->assertSame(EventRegistrationStatus::Registered->value, $data['myRegistrationStatus']);

        $response = $this->apiPost('/api/v1/event-registrations/' . $registrationData['id'] . '/cancel', []);

        $this->assertResponseStatusCodeSame(200);

        $cancelledRegistrationData = $response->toArray();

        $this->assertSame($registrationData['id'], $cancelledRegistrationData['id']);
        $this->assertSame(EventRegistrationStatus::Cancelled->value, $cancelledRegistrationData['status']);
        $this->assertNotNull($cancelledRegistrationData['cancelledAt']);

        $response = $this->apiGet('/api/v1/events/' . $event->getPublicId());

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertSame($event->getPublicId(), $data['id']);
        $this->assertNull($data['myRegistrationStatus'] ?? null);
    }
}
