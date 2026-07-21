<?php

namespace App\Tests\Core\Event\Entity;

use App\Core\Event\Entity\Event;
use App\Core\Event\Enum\EventStatus;
use App\Core\Event\Enum\EventType;
use App\Factory\ClubFactory;
use App\Factory\OrganizationFactory;
use App\Tests\ApiTestCase;

class EventTest extends ApiTestCase
{
    public function testCreateEvent(): void
    {
        $organization = OrganizationFactory::createOne();

        $startsAt = new \DateTimeImmutable('2026-07-10 09:00:00');
        $endsAt = new \DateTimeImmutable('2026-07-10 17:00:00');

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: $startsAt,
            endsAt: $endsAt,
        );

        self::assertSame($organization, $event->getOrganization());
        self::assertNull($event->getClub());

        self::assertSame(EventType::General, $event->getType());
        self::assertSame(EventStatus::Draft, $event->getStatus());

        self::assertSame('Cours d’initiation', $event->getTitle());
        self::assertNull($event->getDescription());
        self::assertNull($event->getLocation());

        self::assertSame($startsAt, $event->getStartsAt());
        self::assertSame($endsAt, $event->getEndsAt());

        self::assertSame('Europe/Zurich', $event->getTimezone());
        self::assertFalse($event->isAllDay());

        self::assertNull($event->getCapacity());
        self::assertFalse($event->hasLimitedCapacity());
        self::assertTrue($event->hasAvailableCapacity());

        self::assertFalse($event->isWaitlistEnabled());

        self::assertNull($event->getRegistrationStartsAt());
        self::assertNull($event->getRegistrationEndsAt());

        self::assertFalse($event->hasRegistrations());
        self::assertSame(0, $event->getRegisteredCount());

        self::assertFalse($event->isPublicRegistrationEnabled());
    }

    public function testCreateEventTrimsTitle(): void
    {
        $organization = OrganizationFactory::createOne();

        $event = Event::create(
            organization: $organization,
            title: '  Sortie du samedi  ',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 12:00:00'),
        );

        self::assertSame('Sortie du samedi', $event->getTitle());
    }

    public function testCreateEventRejectsEmptyTitle(): void
    {
        $organization = OrganizationFactory::createOne();

        $this->expectException(\InvalidArgumentException::class);

        Event::create(
            organization: $organization,
            title: '   ',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 12:00:00'),
        );
    }

    public function testCreateEventRejectsInvalidDateRange(): void
    {
        $organization = OrganizationFactory::createOne();

        $this->expectException(\InvalidArgumentException::class);

        Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 12:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
        );
    }

    public function testCreateEventRejectsSameStartAndEndDate(): void
    {
        $organization = OrganizationFactory::createOne();

        $this->expectException(\InvalidArgumentException::class);

        Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
        );
    }

    public function testAttachEventToClub(): void
    {
        $organization = OrganizationFactory::createOne();
        $club = ClubFactory::new()
            ->forOrganization($organization)
            ->create();


        $event = Event::create(
            organization: $organization,
            title: 'Sortie club',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 12:00:00'),
        );

        $event->attachToClub($club);

        self::assertSame($club, $event->getClub());
    }

    public function testAttachEventRejectsClubFromAnotherOrganization(): void
    {
        $organization = OrganizationFactory::createOne();
        $otherOrganization = OrganizationFactory::createOne();

        $otherClub = ClubFactory::new()
            ->forOrganization($otherOrganization)
            ->create();


        $event = Event::create(
            organization: $organization,
            title: 'Sortie club',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 12:00:00'),
        );

        $this->expectException(\InvalidArgumentException::class);

        $event->attachToClub($otherClub);
    }

    public function testChangeType(): void
    {
        $organization = OrganizationFactory::createOne();

        $event = Event::create(
            organization: $organization,
            title: 'Compétition',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        $event->changeType(EventType::Competition);

        self::assertSame(EventType::Competition, $event->getType());
    }

    public function testChangeStatus(): void
    {
        $organization = OrganizationFactory::createOne();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        self::assertSame(EventStatus::Draft, $event->getStatus());
        self::assertFalse($event->acceptsRegistrations());

        $event->publish();

        self::assertSame(EventStatus::Published, $event->getStatus());
        self::assertTrue($event->acceptsRegistrations());

        $event->cancel();

        self::assertSame(EventStatus::Cancelled, $event->getStatus());
        self::assertFalse($event->acceptsRegistrations());

        $event->archive();

        self::assertSame(EventStatus::Archived, $event->getStatus());
        self::assertFalse($event->acceptsRegistrations());

        $event->revertToDraft();

        self::assertSame(EventStatus::Draft, $event->getStatus());
        self::assertFalse($event->acceptsRegistrations());
    }

    public function testChangeDescriptionAndLocation(): void
    {
        $organization = OrganizationFactory::createOne();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        $event->changeDescription('  Description publique  ');
        $event->changeLocation('  Port de Vidy  ');

        self::assertSame('Description publique', $event->getDescription());
        self::assertSame('Port de Vidy', $event->getLocation());

        $event->changeDescription('   ');
        $event->changeLocation('');

        self::assertNull($event->getDescription());
        self::assertNull($event->getLocation());
    }

    public function testRescheduleEvent(): void
    {
        $organization = OrganizationFactory::createOne();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        $newStartsAt = new \DateTimeImmutable('2026-07-11 10:00:00');
        $newEndsAt = new \DateTimeImmutable('2026-07-11 12:00:00');

        $event->reschedule($newStartsAt, $newEndsAt);

        self::assertSame($newStartsAt, $event->getStartsAt());
        self::assertSame($newEndsAt, $event->getEndsAt());
    }

    public function testRescheduleRejectsInvalidDateRange(): void
    {
        $organization = OrganizationFactory::createOne();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        $this->expectException(\InvalidArgumentException::class);

        $event->reschedule(
            startsAt: new \DateTimeImmutable('2026-07-11 12:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-11 10:00:00'),
        );
    }

    public function testChangeTimezone(): void
    {
        $organization = OrganizationFactory::createOne();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        $event->changeTimezone('Europe/Paris');

        self::assertSame('Europe/Paris', $event->getTimezone());
    }

    public function testChangeTimezoneRejectsInvalidTimezone(): void
    {
        $organization = OrganizationFactory::createOne();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        $this->expectException(\InvalidArgumentException::class);

        $event->changeTimezone('Europe/Invalid');
    }

    public function testAllDayFlag(): void
    {
        $organization = OrganizationFactory::createOne();

        $event = Event::create(
            organization: $organization,
            title: 'Assemblée générale',
            startsAt: new \DateTimeImmutable('2026-07-10 00:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-11 00:00:00'),
        );

        self::assertFalse($event->isAllDay());

        $event->markAsAllDay();

        self::assertTrue($event->isAllDay());

        $event->markAsTimed();

        self::assertFalse($event->isAllDay());
    }

    public function testChangeCapacity(): void
    {
        $organization = OrganizationFactory::createOne();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        self::assertNull($event->getCapacity());
        self::assertFalse($event->hasLimitedCapacity());

        $event->changeCapacity(20);

        self::assertSame(20, $event->getCapacity());
        self::assertTrue($event->hasLimitedCapacity());

        $event->changeCapacity(null);

        self::assertNull($event->getCapacity());
        self::assertFalse($event->hasLimitedCapacity());
    }

    public function testChangeCapacityRejectsZero(): void
    {
        $organization = OrganizationFactory::createOne();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        $this->expectException(\InvalidArgumentException::class);

        $event->changeCapacity(0);
    }

    public function testChangeCapacityRejectsNegativeValue(): void
    {
        $organization = OrganizationFactory::createOne();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        $this->expectException(\InvalidArgumentException::class);

        $event->changeCapacity(-1);
    }

    public function testWaitlistFlag(): void
    {
        $organization = OrganizationFactory::createOne();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        self::assertFalse($event->isWaitlistEnabled());

        $event->enableWaitlist();

        self::assertTrue($event->isWaitlistEnabled());

        $event->disableWaitlist();

        self::assertFalse($event->isWaitlistEnabled());
    }

    public function testChangeRegistrationWindow(): void
    {
        $organization = OrganizationFactory::createOne();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        $registrationStartsAt = new \DateTimeImmutable('2026-07-01 09:00:00');
        $registrationEndsAt = new \DateTimeImmutable('2026-07-09 18:00:00');

        $event->changeRegistrationWindow($registrationStartsAt, $registrationEndsAt);

        self::assertSame($registrationStartsAt, $event->getRegistrationStartsAt());
        self::assertSame($registrationEndsAt, $event->getRegistrationEndsAt());
    }

    public function testChangeRegistrationWindowRejectsInvalidDateRange(): void
    {
        $organization = OrganizationFactory::createOne();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        $this->expectException(\InvalidArgumentException::class);

        $event->changeRegistrationWindow(
            startsAt: new \DateTimeImmutable('2026-07-09 18:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-01 09:00:00'),
        );
    }

    public function testPublicRegistrationFlag(): void
    {
        $organization = OrganizationFactory::createOne();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        self::assertFalse($event->isPublicRegistrationEnabled());

        $event->enablePublicRegistration();

        self::assertTrue($event->isPublicRegistrationEnabled());

        $event->disablePublicRegistration();

        self::assertFalse($event->isPublicRegistrationEnabled());
    }

}
