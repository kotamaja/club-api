<?php

namespace App\Tests\Core\Event\Entity;

use App\Core\Event\Entity\Event;
use App\Core\Event\Entity\EventRegistration;
use App\Core\Event\Enum\EventRegistrationStatus;
use App\Factory\ClubFactory;
use App\Factory\MembershipFactory;
use App\Factory\OrganizationFactory;
use App\Factory\PersonFactory;
use App\Tests\ApiTestCase;

final class EventRegistrationTest extends ApiTestCase
{
    public function testRegisterPersonToEvent(): void
    {
        $organization = OrganizationFactory::createOne();
        $person = PersonFactory::new()->forOrganization($organization)->create();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        $now = new \DateTimeImmutable('2026-07-01 10:00:00');

        $registration = EventRegistration::register(
            event: $event,
            person: $person,
            membership: null,
            now: $now,
        );

        self::assertSame($event, $registration->getEvent());
        self::assertSame($person, $registration->getPerson());
        self::assertNull($registration->getMembership());

        self::assertSame(EventRegistrationStatus::Registered, $registration->getStatus());
        self::assertTrue($registration->isActive());
        self::assertTrue($registration->consumesCapacity());

        self::assertSame($now, $registration->getRequestedAt());
        self::assertSame($now, $registration->getConfirmedAt());
        self::assertNull($registration->getCancelledAt());
        self::assertNull($registration->getNote());
    }

    public function testWaitlistPersonToEvent(): void
    {
        $organization = OrganizationFactory::createOne();
        $person = PersonFactory::new()->forOrganization($organization)->create();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        $now = new \DateTimeImmutable('2026-07-01 10:00:00');

        $registration = EventRegistration::waitlist(
            event: $event,
            person: $person,
            membership: null,
            now: $now,
        );

        self::assertSame(EventRegistrationStatus::Waitlisted, $registration->getStatus());
        self::assertTrue($registration->isActive());
        self::assertFalse($registration->consumesCapacity());

        self::assertSame($now, $registration->getRequestedAt());
        self::assertNull($registration->getConfirmedAt());
        self::assertNull($registration->getCancelledAt());
    }

    public function testCancelRegisteredRegistration(): void
    {
        $organization = OrganizationFactory::createOne();
        $person = PersonFactory::new()->forOrganization($organization)->create();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        $requestedAt = new \DateTimeImmutable('2026-07-01 10:00:00');

        $registration = EventRegistration::register(
            event: $event,
            person: $person,
            membership: null,
            now: $requestedAt,
        );

        $cancelledAt = new \DateTimeImmutable('2026-07-02 12:00:00');

        $registration->cancel($cancelledAt);

        self::assertSame(EventRegistrationStatus::Cancelled, $registration->getStatus());
        self::assertFalse($registration->isActive());
        self::assertFalse($registration->consumesCapacity());

        self::assertSame($cancelledAt, $registration->getCancelledAt());
        self::assertSame($requestedAt, $registration->getRequestedAt());
        self::assertSame($requestedAt, $registration->getConfirmedAt());
    }
    public function testCancelWaitlistedRegistration(): void
    {
        $organization = OrganizationFactory::createOne();
        $person = PersonFactory::new()->forOrganization($organization)->create();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        $registration = EventRegistration::waitlist(
            event: $event,
            person: $person,
            membership: null,
            now: new \DateTimeImmutable('2026-07-01 10:00:00'),
        );

        $cancelledAt = new \DateTimeImmutable('2026-07-02 12:00:00');

        $registration->cancel($cancelledAt);

        self::assertSame(EventRegistrationStatus::Cancelled, $registration->getStatus());
        self::assertFalse($registration->isActive());
        self::assertFalse($registration->consumesCapacity());

        self::assertSame($cancelledAt, $registration->getCancelledAt());
        self::assertNull($registration->getConfirmedAt());
    }

    public function testCancelAlreadyCancelledRegistrationDoesNothing(): void
    {
        $organization = OrganizationFactory::createOne();
        $person = PersonFactory::new()->forOrganization($organization)->create();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        $registration = EventRegistration::register(
            event: $event,
            person: $person,
            membership: null,
            now: new \DateTimeImmutable('2026-07-01 10:00:00'),
        );

        $firstCancelledAt = new \DateTimeImmutable('2026-07-02 12:00:00');
        $secondCancelledAt = new \DateTimeImmutable('2026-07-03 12:00:00');

        $registration->cancel($firstCancelledAt);
        $registration->cancel($secondCancelledAt);

        self::assertSame($firstCancelledAt, $registration->getCancelledAt());
    }

    public function testPromoteWaitlistedRegistration(): void
    {
        $organization = OrganizationFactory::createOne();
        $person = PersonFactory::new()->forOrganization($organization)->create();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        $registration = EventRegistration::waitlist(
            event: $event,
            person: $person,
            membership: null,
            now: new \DateTimeImmutable('2026-07-01 10:00:00'),
        );

        $confirmedAt = new \DateTimeImmutable('2026-07-02 09:00:00');

        $registration->promoteFromWaitlist($confirmedAt);

        self::assertSame(EventRegistrationStatus::Registered, $registration->getStatus());
        self::assertTrue($registration->isActive());
        self::assertTrue($registration->consumesCapacity());

        self::assertSame($confirmedAt, $registration->getConfirmedAt());
        self::assertNull($registration->getCancelledAt());
    }

    public function testPromoteRegisteredRegistrationIsRejected(): void
    {
        $organization = OrganizationFactory::createOne();
        $person = PersonFactory::new()->forOrganization($organization)->create();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        $registration = EventRegistration::register(
            event: $event,
            person: $person,
            membership: null,
            now: new \DateTimeImmutable('2026-07-01 10:00:00'),
        );

        $this->expectException(\LogicException::class);

        $registration->promoteFromWaitlist(new \DateTimeImmutable('2026-07-02 09:00:00'));
    }

    public function testChangeNote(): void
    {
        $organization = OrganizationFactory::createOne();
        $person = PersonFactory::new()->forOrganization($organization)->create();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        $registration = EventRegistration::register(
            event: $event,
            person: $person,
            membership: null,
            now: new \DateTimeImmutable('2026-07-01 10:00:00'),
        );

        $registration->changeNote('  Préfère le matin  ');

        self::assertSame('Préfère le matin', $registration->getNote());

        $registration->changeNote('   ');

        self::assertNull($registration->getNote());
    }

    public function testRegisterWithMembership(): void
    {
        $organization = OrganizationFactory::createOne();

        $club = ClubFactory::new()
            ->forOrganization($organization)
            ->create();

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $membership = MembershipFactory::new()
            ->forClub($club)
            ->forPerson($person)
            ->create();

        $event = Event::create(
            organization: $organization,
            title: 'Sortie club',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );
        $event->attachToClub($club);

        $registration = EventRegistration::register(
            event: $event,
            person: $person,
            membership: $membership,
            now: new \DateTimeImmutable('2026-07-01 10:00:00'),
        );

        self::assertSame($membership, $registration->getMembership());
    }

    public function testRegisterRejectsMembershipFromAnotherPerson(): void
    {
        $organization = OrganizationFactory::createOne();

        $club = ClubFactory::new()
            ->forOrganization($organization)
            ->create();

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $otherPerson = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $membership = MembershipFactory::new()
            ->forClub($club)
            ->forPerson($otherPerson)
            ->create();

        $event = Event::create(
            organization: $organization,
            title: 'Sortie club',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );
        $event->attachToClub($club);

        $this->expectException(\InvalidArgumentException::class);

        EventRegistration::register(
            event: $event,
            person: $person,
            membership: $membership,
            now: new \DateTimeImmutable('2026-07-01 10:00:00'),
        );
    }

    public function testRegisterRejectsMembershipFromAnotherClubWhenEventHasClub(): void
    {
        $organization = OrganizationFactory::createOne();

        $eventClub = ClubFactory::new()
            ->forOrganization($organization)
            ->create();

        $otherClub = ClubFactory::new()
            ->forOrganization($organization)
            ->create();

        $person = PersonFactory::new()
            ->forOrganization($organization)
            ->create();

        $membership = MembershipFactory::new()
            ->forClub($otherClub)
            ->forPerson($person)
            ->create();

        $event = Event::create(
            organization: $organization,
            title: 'Sortie club',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );
        $event->attachToClub($eventClub);

        $this->expectException(\InvalidArgumentException::class);

        EventRegistration::register(
            event: $event,
            person: $person,
            membership: $membership,
            now: new \DateTimeImmutable('2026-07-01 10:00:00'),
        );
    }
}
