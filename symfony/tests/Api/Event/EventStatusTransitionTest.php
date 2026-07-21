<?php

namespace App\Tests\Api\Event;

use App\Core\Event\Enum\EventStatus;
use App\Core\Event\Enum\EventType;
use App\Factory\EventFactory;
use App\Factory\OrganizationFactory;
use App\Tests\ApiTestCase;

final class EventStatusTransitionTest extends ApiTestCase
{
    public function testPublishEvent(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create([
                'type' => EventType::Course,
                'title' => 'Cours débutants',
                'description' => 'Cours pour nouveaux rameurs.',
                'location' => 'Club house',
                'startsAt' => new \DateTimeImmutable('2026-09-12T09:00:00+02:00'),
                'endsAt' => new \DateTimeImmutable('2026-09-12T12:00:00+02:00'),
                'timezone' => 'Europe/Zurich',
                'capacity' => 12,
                'waitlistEnabled' => true,
                'publicRegistrationEnabled' => true,
                'registrationStartsAt' => new \DateTimeImmutable('2026-08-01T08:00:00+02:00'),
                'registrationEndsAt' => new \DateTimeImmutable('2026-09-10T23:59:00+02:00'),
            ]);

        $response = $this->apiPost('/api/v1/events/' . $event->getPublicId() . '/publish', []);

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertArrayHasValidUlid($data, 'id');
        $this->assertSame($event->getPublicId(), $data['id']);

        $this->assertSame('Cours débutants', $data['title']);
        $this->assertSame('Cours pour nouveaux rameurs.', $data['description']);
        $this->assertSame('Club house', $data['location']);

        $this->assertSame(EventType::Course->value, $data['type']);
        $this->assertSame(EventStatus::Published->value, $data['status']);

        $this->assertSame('2026-09-12T09:00:00+02:00', $data['startsAt']);
        $this->assertSame('2026-09-12T12:00:00+02:00', $data['endsAt']);
        $this->assertSame('Europe/Zurich', $data['timezone']);

        $this->assertSame(12, $data['capacity']);
        $this->assertSame(0, $data['registeredCount']);
        $this->assertSame(0, $data['waitlistedCount']);
        $this->assertTrue($data['waitlistEnabled']);

        $this->assertTrue($data['publicRegistrationEnabled']);
        $this->assertSame('2026-08-01T08:00:00+02:00', $data['registrationStartsAt']);
        $this->assertSame('2026-09-10T23:59:00+02:00', $data['registrationEndsAt']);

        $this->assertNull($data['myRegistrationStatus'] ?? null);
    }

    public function testCancelEvent(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create([
                'title' => 'Sortie sur le lac',
                'startsAt' => new \DateTimeImmutable('2026-09-20T08:30:00+02:00'),
                'endsAt' => new \DateTimeImmutable('2026-09-20T11:30:00+02:00'),
            ]);

        $response = $this->apiPost('/api/v1/events/' . $event->getPublicId() . '/cancel', []);

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertArrayHasValidUlid($data, 'id');
        $this->assertSame($event->getPublicId(), $data['id']);

        $this->assertSame('Sortie sur le lac', $data['title']);
        $this->assertSame(EventStatus::Cancelled->value, $data['status']);

        $this->assertSame('2026-09-20T08:30:00+02:00', $data['startsAt']);
        $this->assertSame('2026-09-20T11:30:00+02:00', $data['endsAt']);

        $this->assertNull($data['myRegistrationStatus'] ?? null);
    }

    public function testArchiveEvent(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create([
                'title' => 'Assemblée générale',
                'startsAt' => new \DateTimeImmutable('2026-10-03T18:30:00+02:00'),
                'endsAt' => new \DateTimeImmutable('2026-10-03T21:00:00+02:00'),
            ]);

        $response = $this->apiPost('/api/v1/events/' . $event->getPublicId() . '/archive', []);

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertArrayHasValidUlid($data, 'id');
        $this->assertSame($event->getPublicId(), $data['id']);

        $this->assertSame('Assemblée générale', $data['title']);
        $this->assertSame(EventStatus::Archived->value, $data['status']);

        $this->assertSame('2026-10-03T18:30:00+02:00', $data['startsAt']);
        $this->assertSame('2026-10-03T21:00:00+02:00', $data['endsAt']);

        $this->assertNull($data['myRegistrationStatus'] ?? null);
    }

    public function testPublishEventFromAnotherOrganizationReturnsNotFound(): void
    {
        $this->getAuthenticatedOrganizationContext();

        $otherOrganization = OrganizationFactory::createOne();

        $event = EventFactory::new()
            ->forOrganization($otherOrganization)
            ->create();

        $this->apiPost('/api/v1/events/' . $event->getPublicId() . '/publish', []);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCancelEventFromAnotherOrganizationReturnsNotFound(): void
    {
        $this->getAuthenticatedOrganizationContext();

        $otherOrganization = OrganizationFactory::createOne();

        $event = EventFactory::new()
            ->forOrganization($otherOrganization)
            ->create();

        $this->apiPost('/api/v1/events/' . $event->getPublicId() . '/cancel', []);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testArchiveEventFromAnotherOrganizationReturnsNotFound(): void
    {
        $this->getAuthenticatedOrganizationContext();

        $otherOrganization = OrganizationFactory::createOne();

        $event = EventFactory::new()
            ->forOrganization($otherOrganization)
            ->create();

        $this->apiPost('/api/v1/events/' . $event->getPublicId() . '/archive', []);

        $this->assertResponseStatusCodeSame(404);
    }
}
