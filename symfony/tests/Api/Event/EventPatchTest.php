<?php

namespace App\Tests\Api\Event;

use App\Core\Event\Enum\EventStatus;
use App\Core\Event\Enum\EventType;
use App\Factory\ClubFactory;
use App\Factory\EventFactory;
use App\Tests\ApiTestCase;

final class EventPatchTest extends ApiTestCase
{
    public function testPatchEvent(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $club = ClubFactory::new()
            ->forOrganization($organization)
            ->create();

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create([
                'title' => 'Ancien titre',
                'description' => 'Ancienne description',
                'location' => 'Ancien lieu',
                'startsAt' => new \DateTimeImmutable('2026-09-12T09:00:00+02:00'),
                'endsAt' => new \DateTimeImmutable('2026-09-12T12:00:00+02:00'),
            ]);

        $response = $this->apiPatch('/api/v1/events/' . $event->getPublicId(), [
            'clubId' => $club->getPublicId(),
            'type' => EventType::Course->value,
            'title' => 'Nouveau titre',
            'description' => 'Nouvelle description',
            'location' => 'Nouveau lieu',
            'startsAt' => '2026-09-13T10:00:00+02:00',
            'endsAt' => '2026-09-13T11:30:00+02:00',
            'timezone' => 'Europe/Zurich',
            'allDay' => false,
            'capacity' => 20,
            'waitlistEnabled' => true,
            'publicRegistrationEnabled' => true,
            'registrationStartsAt' => '2026-08-01T08:00:00+02:00',
            'registrationEndsAt' => '2026-09-10T23:59:00+02:00',
        ]);

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertArrayHasValidUlid($data, 'id');
        $this->assertSame($event->getPublicId(), $data['id']);

        $this->assertSame('Nouveau titre', $data['title']);
        $this->assertSame('Nouvelle description', $data['description']);
        $this->assertSame('Nouveau lieu', $data['location']);

        $this->assertSame(EventType::Course->value, $data['type']);
        $this->assertSame(EventStatus::Draft->value, $data['status']);

        $this->assertSame($club->getPublicId(), $data['clubId']);
        $this->assertSame($club->getName(), $data['clubName']);

        $this->assertSame('2026-09-13T10:00:00+02:00', $data['startsAt']);
        $this->assertSame('2026-09-13T11:30:00+02:00', $data['endsAt']);
        $this->assertSame('Europe/Zurich', $data['timezone']);
        $this->assertFalse($data['allDay']);

        $this->assertSame(20, $data['capacity']);
        $this->assertSame(0, $data['registeredCount']);
        $this->assertSame(0, $data['waitlistedCount']);
        $this->assertTrue($data['waitlistEnabled']);

        $this->assertTrue($data['publicRegistrationEnabled']);
        $this->assertSame('2026-08-01T08:00:00+02:00', $data['registrationStartsAt']);
        $this->assertSame('2026-09-10T23:59:00+02:00', $data['registrationEndsAt']);

        $this->assertNull($data['myRegistrationStatus']);
    }

    public function testPatchPartialEventKeepsMissingFieldsUnchanged(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $club = ClubFactory::new()
            ->forOrganization($organization)
            ->create();

        $event = EventFactory::new()
            ->forClub($club)
            ->create([
                'type' => EventType::Training,
                'title' => 'Ancien titre',
                'description' => 'Description conservée',
                'location' => 'Lieu conservé',
                'startsAt' => new \DateTimeImmutable('2026-09-12T09:00:00+02:00'),
                'endsAt' => new \DateTimeImmutable('2026-09-12T12:00:00+02:00'),
                'timezone' => 'Europe/Zurich',
                'allDay' => false,
                'capacity' => 12,
                'waitlistEnabled' => true,
                'publicRegistrationEnabled' => true,
                'registrationStartsAt' => new \DateTimeImmutable('2026-08-01T08:00:00+02:00'),
                'registrationEndsAt' => new \DateTimeImmutable('2026-09-10T23:59:00+02:00'),
            ]);

        $response = $this->apiPatch('/api/v1/events/' . $event->getPublicId(), [
            'title' => 'Nouveau titre',
        ]);

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertSame($event->getPublicId(), $data['id']);

        $this->assertSame('Nouveau titre', $data['title']);
        $this->assertSame('Description conservée', $data['description']);
        $this->assertSame('Lieu conservé', $data['location']);

        $this->assertSame(EventType::Training->value, $data['type']);
        $this->assertSame(EventStatus::Draft->value, $data['status']);

        $this->assertSame($club->getPublicId(), $data['clubId']);
        $this->assertSame($club->getName(), $data['clubName']);

        $this->assertSame('2026-09-12T09:00:00+02:00', $data['startsAt']);
        $this->assertSame('2026-09-12T12:00:00+02:00', $data['endsAt']);
        $this->assertSame('Europe/Zurich', $data['timezone']);
        $this->assertFalse($data['allDay']);

        $this->assertSame(12, $data['capacity']);
        $this->assertTrue($data['waitlistEnabled']);
        $this->assertTrue($data['publicRegistrationEnabled']);
        $this->assertSame('2026-08-01T08:00:00+02:00', $data['registrationStartsAt']);
        $this->assertSame('2026-09-10T23:59:00+02:00', $data['registrationEndsAt']);
    }

    public function testPatchCanClearNullableFields(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $club = ClubFactory::new()
            ->forOrganization($organization)
            ->create();

        $event = EventFactory::new()
            ->forClub($club)
            ->create([
                'description' => 'Description à supprimer',
                'location' => 'Lieu à supprimer',
                'capacity' => 12,
                'registrationStartsAt' => new \DateTimeImmutable('2026-08-01T08:00:00+02:00'),
                'registrationEndsAt' => new \DateTimeImmutable('2026-09-10T23:59:00+02:00'),
            ]);

        $response = $this->apiPatch('/api/v1/events/' . $event->getPublicId(), [
            'clubId' => null,
            'description' => null,
            'location' => null,
            'capacity' => null,
            'registrationStartsAt' => null,
            'registrationEndsAt' => null,
        ]);

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertSame($event->getPublicId(), $data['id']);

        $this->assertNull($data['clubId']);
        $this->assertNull($data['clubName']);
        $this->assertNull($data['description']);
        $this->assertNull($data['location']);
        $this->assertNull($data['capacity']);
        $this->assertNull($data['registrationStartsAt']);
        $this->assertNull($data['registrationEndsAt']);
    }

    public function testPatchStartDateOnlyKeepsEndDate(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create([
                'startsAt' => new \DateTimeImmutable('2026-09-12T09:00:00+02:00'),
                'endsAt' => new \DateTimeImmutable('2026-09-12T12:00:00+02:00'),
            ]);

        $response = $this->apiPatch('/api/v1/events/' . $event->getPublicId(), [
            'startsAt' => '2026-09-12T10:00:00+02:00',
        ]);

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertSame($event->getPublicId(), $data['id']);
        $this->assertSame('2026-09-12T10:00:00+02:00', $data['startsAt']);
        $this->assertSame('2026-09-12T12:00:00+02:00', $data['endsAt']);
    }

    public function testPatchEndDateOnlyKeepsStartDate(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create([
                'startsAt' => new \DateTimeImmutable('2026-09-12T09:00:00+02:00'),
                'endsAt' => new \DateTimeImmutable('2026-09-12T12:00:00+02:00'),
            ]);

        $response = $this->apiPatch('/api/v1/events/' . $event->getPublicId(), [
            'endsAt' => '2026-09-12T13:00:00+02:00',
        ]);

        $this->assertResponseStatusCodeSame(200);

        $data = $response->toArray();

        $this->assertSame($event->getPublicId(), $data['id']);
        $this->assertSame('2026-09-12T09:00:00+02:00', $data['startsAt']);
        $this->assertSame('2026-09-12T13:00:00+02:00', $data['endsAt']);
    }

    public function testPatchRejectsInvalidDateRange(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create([
                'startsAt' => new \DateTimeImmutable('2026-09-12T09:00:00+02:00'),
                'endsAt' => new \DateTimeImmutable('2026-09-12T12:00:00+02:00'),
            ]);

        $this->apiPatch('/api/v1/events/' . $event->getPublicId(), [
            'startsAt' => '2026-09-12T13:00:00+02:00',
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testPatchCannotUseClubFromAnotherOrganization(): void
    {
        $organization = $this->getAuthenticatedOrganizationContext()->organization;

        $event = EventFactory::new()
            ->forOrganization($organization)
            ->create();

        $otherOrganization = \App\Factory\OrganizationFactory::createOne();

        $otherClub = ClubFactory::new()
            ->forOrganization($otherOrganization)
            ->create();

        $this->apiPatch('/api/v1/events/' . $event->getPublicId(), [
            'clubId' => $otherClub->getPublicId(),
        ]);

        $this->assertResponseStatusCodeSame(404);
    }
}
