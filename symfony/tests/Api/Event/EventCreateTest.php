<?php

namespace App\Tests\Api\Event;

use App\Core\Event\Enum\EventStatus;
use App\Core\Event\Enum\EventType;
use App\Factory\ClubFactory;
use App\Factory\OrganizationFactory;
use App\Tests\ApiTestCase;

final class EventCreateTest extends ApiTestCase
{
    public function testCreateMinimalEvent(): void
    {
        $this->getAuthenticatedOrganizationContext();

        $response = $this->apiPost('/api/v1/events', [
            'title' => 'Initiation aviron',
            'startsAt' => '2026-09-12T09:00:00+00:00',
            'endsAt' => '2026-09-12T12:00:00+00:00',
        ]);

        $this->assertResponseStatusCodeSame(201);

        $data = $response->toArray();

        $this->assertArrayHasValidUlid($data, 'id');

        $this->assertSame('Initiation aviron', $data['title']);
        $this->assertNull($data['description']);
        $this->assertNull($data['location']);

        $this->assertSame(EventType::General->value, $data['type']);
        $this->assertSame(EventStatus::Draft->value, $data['status']);

        $this->assertNull($data['clubId']);
        $this->assertNull($data['clubName']);

        $this->assertSame('2026-09-12T09:00:00+00:00', $data['startsAt']);
        $this->assertSame('2026-09-12T12:00:00+00:00', $data['endsAt']);
        $this->assertSame('Europe/Zurich', $data['timezone']);
        $this->assertFalse($data['allDay']);

        $this->assertNull($data['capacity']);
        $this->assertSame(0, $data['registeredCount']);
        $this->assertSame(0, $data['waitlistedCount']);
        $this->assertFalse($data['waitlistEnabled']);

        $this->assertFalse($data['publicRegistrationEnabled']);
        $this->assertNull($data['registrationStartsAt']);
        $this->assertNull($data['registrationEndsAt']);

        $this->assertNull($data['myRegistrationStatus']);
    }

    public function testCreateEventAttachedToClub(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $club = ClubFactory::new()
            ->forOrganization($organization)
            ->create();

        $response = $this->apiPost('/api/v1/events', [
            'clubId' => $club->getPublicId(),
            'type' => EventType::Course->value,
            'title' => 'Cours débutants',
            'description' => 'Cours pour nouveaux rameurs.',
            'location' => 'Club house',
            'startsAt' => '2026-09-13T10:00:00+00:00',
            'endsAt' => '2026-09-13T11:30:00+00:00',
            'timezone' => 'Europe/Zurich',
            'allDay' => false,
            'capacity' => 12,
            'waitlistEnabled' => true,
            'publicRegistrationEnabled' => true,
            'registrationStartsAt' => '2026-08-01T08:00:00+00:00',
            'registrationEndsAt' => '2026-09-10T23:59:00+00:00',
        ]);

        $this->assertResponseStatusCodeSame(201);

        $data = $response->toArray();

        $this->assertArrayHasValidUlid($data, 'id');

        $this->assertSame('Cours débutants', $data['title']);
        $this->assertSame('Cours pour nouveaux rameurs.', $data['description']);
        $this->assertSame('Club house', $data['location']);

        $this->assertSame(EventType::Course->value, $data['type']);
        $this->assertSame(EventStatus::Draft->value, $data['status']);

        $this->assertSame($club->getPublicId(), $data['clubId']);
        $this->assertSame($club->getName(), $data['clubName']);

        $this->assertSame('2026-09-13T10:00:00+00:00', $data['startsAt']);
        $this->assertSame('2026-09-13T11:30:00+00:00', $data['endsAt']);
        $this->assertSame('Europe/Zurich', $data['timezone']);
        $this->assertFalse($data['allDay']);

        $this->assertSame(12, $data['capacity']);
        $this->assertSame(0, $data['registeredCount']);
        $this->assertSame(0, $data['waitlistedCount']);
        $this->assertTrue($data['waitlistEnabled']);

        $this->assertTrue($data['publicRegistrationEnabled']);
        $this->assertSame('2026-08-01T08:00:00+00:00', $data['registrationStartsAt']);
        $this->assertSame('2026-09-10T23:59:00+00:00', $data['registrationEndsAt']);

        $this->assertNull($data['myRegistrationStatus']);
    }

    public function testCreateRejectsClubOutsideCurrentOrganization(): void
    {
        $this->getAuthenticatedOrganizationContext();

        $otherOrganization = OrganizationFactory::createOne();

        $otherClub = ClubFactory::new()
            ->forOrganization($otherOrganization)
            ->create();

        $this->apiPost('/api/v1/events', [
            'clubId' => $otherClub->getPublicId(),
            'title' => 'Event invalide',
            'startsAt' => '2026-09-12T09:00:00+00:00',
            'endsAt' => '2026-09-12T12:00:00+00:00',
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateRejectsInvalidDateRange(): void
    {
        $this->getAuthenticatedOrganizationContext();

        $this->apiPost('/api/v1/events', [
            'title' => 'Event invalide',
            'startsAt' => '2026-09-12T12:00:00+00:00',
            'endsAt' => '2026-09-12T09:00:00+00:00',
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateRejectsMissingTitle(): void
    {
        $this->getAuthenticatedOrganizationContext();

        $this->apiPost('/api/v1/events', [
            'startsAt' => '2026-09-12T09:00:00+00:00',
            'endsAt' => '2026-09-12T12:00:00+00:00',
        ]);

        $this->assertResponseStatusCodeSame(422);
    }
}
