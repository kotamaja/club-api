<?php

namespace App\Tests\Api\EventRegistration;

use App\Core\Event\Enum\EventRegistrationStatus;
use App\Factory\EventFactory;
use App\Factory\EventRegistrationFactory;
use App\Factory\OrganizationFactory;
use App\Factory\PersonFactory;
use App\Tests\ApiTestCase;

final class EventRegistrationCollectionTest extends ApiTestCase
{
    public function testGetEventRegistrations(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create([
                'title' => 'Cours débutants',
                'startsAt' => new \DateTimeImmutable('2026-09-12T09:00:00+02:00'),
                'endsAt' => new \DateTimeImmutable('2026-09-12T12:00:00+02:00'),
            ]);

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

        $response = $this->apiGet('/api/v1/events/' . $event->getPublicId() . '/registrations');

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('pagination', $data);

        $items = $data['items'];
        $pagination = $data['pagination'];

        $this->assertCount(1, $items);

        $this->assertArrayHasValidUlid($items[0], 'id');
        $this->assertSame($registration->getPublicId(), $items[0]['id']);

        $this->assertSame($event->getPublicId(), $items[0]['eventId']);
        $this->assertSame('Cours débutants', $items[0]['eventTitle']);

        $this->assertSame($person->getPublicId(), $items[0]['personId']);
        $this->assertSame('Jean', $items[0]['personFirstname']);
        $this->assertSame('Dupont', $items[0]['personLastname']);

        $this->assertNull($items[0]['membershipId'] ?? null);

        $this->assertSame(EventRegistrationStatus::Registered->value, $items[0]['status']);
        $this->assertSame('2026-09-01T10:00:00+02:00', $items[0]['requestedAt']);
        $this->assertNull($items[0]['cancelledAt'] ?? null);

        $this->assertIsArray($pagination);
    }

    public function testGetEventRegistrationsReturnsOnlyRegistrationsForCurrentEvent(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create();

        $otherEvent = EventFactory::new()
            ->forOrganization($organization)
            ->create();

        $person1 = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $person2 = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $registration = EventRegistrationFactory::new()
            ->forEvent($event)
            ->forPerson($person1)
            ->registered()
            ->create();

        EventRegistrationFactory::new()
            ->forEvent($otherEvent)
            ->forPerson($person2)
            ->registered()
            ->create();

        $response = $this->apiGet('/api/v1/events/' . $event->getPublicId() . '/registrations');

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('pagination', $data);

        $items = $data['items'];
        $pagination = $data['pagination'];

        $this->assertCount(1, $items);

        $this->assertSame($registration->getPublicId(), $items[0]['id']);
        $this->assertSame($event->getPublicId(), $items[0]['eventId']);

        $this->assertIsArray($pagination);
    }

    /**
     * Ensures that registrations of an event from another organization are not visible.
     */
    public function testGetEventRegistrationsFromAnotherOrganizationReturnsNotFound(): void
    {
        $this->getAuthenticatedOrganizationContext();

        $otherOrganization = OrganizationFactory::createOne();

        $event = EventFactory::new()
            ->forOrganization($otherOrganization)
            ->create();

        $response = $this->apiGet('/api/v1/events/' . $event->getPublicId() . '/registrations');

        self::assertResponseStatusCodeSame(404);

        $data = $response->toArray(false);

        self::assertResponseStatusCodeSame(404);
    }

    public function testGetEventRegistrationsCanFilterByStatus(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create();

        $registeredPerson = PersonFactory::new()
            ->forOrganization($organization)
            ->create([
                'firstname' => 'Jean',
                'lastname' => 'Dupont',
            ]);

        $waitlistedPerson = PersonFactory::new()
            ->forOrganization($organization)
            ->create([
                'firstname' => 'Marie',
                'lastname' => 'Martin',
            ]);

        $registeredRegistration = EventRegistrationFactory::new()
            ->forEvent($event)
            ->forPerson($registeredPerson)
            ->registered()
            ->create();

        EventRegistrationFactory::new()
            ->forEvent($event)
            ->forPerson($waitlistedPerson)
            ->waitlisted()
            ->create();

        $response = $this->apiGet(
            '/api/v1/events/' . $event->getPublicId() . '/registrations?status=' . EventRegistrationStatus::Registered->value,
        );

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('pagination', $data);

        $items = $data['items'];

        $this->assertCount(1, $items);
        $this->assertSame($registeredRegistration->getPublicId(), $items[0]['id']);
        $this->assertSame(EventRegistrationStatus::Registered->value, $items[0]['status']);
        $this->assertSame('Jean', $items[0]['personFirstname']);
        $this->assertSame('Dupont', $items[0]['personLastname']);

        $this->assertSame(1, $data['pagination']['totalItems']);
    }

    public function testGetEventRegistrationsCanOrderByRequestedAt(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create();

        $firstPerson = PersonFactory::new()
            ->forOrganization($organization)
            ->create([
                'firstname' => 'Jean',
                'lastname' => 'Dupont',
            ]);

        $secondPerson = PersonFactory::new()
            ->forOrganization($organization)
            ->create([
                'firstname' => 'Marie',
                'lastname' => 'Martin',
            ]);

        $firstRegistration = EventRegistrationFactory::new()
            ->forEvent($event)
            ->forPerson($firstPerson)
            ->registered()
            ->create([
                'requestedAt' => new \DateTimeImmutable('2026-09-01T10:00:00+02:00'),
            ]);

        $secondRegistration = EventRegistrationFactory::new()
            ->forEvent($event)
            ->forPerson($secondPerson)
            ->registered()
            ->create([
                'requestedAt' => new \DateTimeImmutable('2026-09-02T10:00:00+02:00'),
            ]);

        $response = $this->apiGet(
            '/api/v1/events/' . $event->getPublicId() . '/registrations?orderRequestedAt=desc',
        );

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('pagination', $data);

        $items = $data['items'];

        $this->assertCount(2, $items);

        $this->assertSame($secondRegistration->getPublicId(), $items[0]['id']);
        $this->assertSame('2026-09-02T10:00:00+02:00', $items[0]['requestedAt']);

        $this->assertSame($firstRegistration->getPublicId(), $items[1]['id']);
        $this->assertSame('2026-09-01T10:00:00+02:00', $items[1]['requestedAt']);

        $this->assertSame(2, $data['pagination']['totalItems']);
    }

    public function testGetEventRegistrationsCanOrderByPersonLastname(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create();

        $zeller = PersonFactory::new()
            ->forOrganization($organization)
            ->create([
                'firstname' => 'Anna',
                'lastname' => 'Zeller',
            ]);

        $andrey = PersonFactory::new()
            ->forOrganization($organization)
            ->create([
                'firstname' => 'Marc',
                'lastname' => 'Andrey',
            ]);

        EventRegistrationFactory::new()
            ->forEvent($event)
            ->forPerson($zeller)
            ->registered()
            ->create();

        EventRegistrationFactory::new()
            ->forEvent($event)
            ->forPerson($andrey)
            ->registered()
            ->create();

        $response = $this->apiGet(
            '/api/v1/events/' . $event->getPublicId() . '/registrations?orderPersonLastname=asc',
        );

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $items = $data['items'];

        $this->assertCount(2, $items);

        $this->assertSame('Andrey', $items[0]['personLastname']);
        $this->assertSame('Zeller', $items[1]['personLastname']);
    }
}
