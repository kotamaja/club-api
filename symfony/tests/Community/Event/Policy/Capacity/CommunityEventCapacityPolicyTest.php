<?php

namespace App\Tests\Community\Event\Policy\Capacity;

use App\Community\Event\Policy\Capacity\CommunityEventCapacityPolicy;
use App\Core\Event\Entity\Event;
use App\Core\Event\Entity\EventRegistration;
use App\Core\Event\Exception\EventCapacityExceededException;
use App\Factory\OrganizationFactory;
use App\Factory\PersonFactory;
use App\Tests\ApiTestCase;

final class CommunityEventCapacityPolicyTest extends ApiTestCase
{
    private CommunityEventCapacityPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new CommunityEventCapacityPolicy();
    }

    public function testUnlimitedEventHasAvailableCapacity(): void
    {
        $organization = OrganizationFactory::createOne();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );

        self::assertTrue($this->policy->hasAvailableCapacity($event));

        $this->policy->assertHasAvailableCapacity($event);

        self::assertTrue(true);
    }

    public function testLimitedEventWithAvailableCapacityIsAccepted(): void
    {
        $organization = OrganizationFactory::createOne();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );
        $event->changeCapacity(2);

        $person = PersonFactory::new()->forOrganization($organization)->create();

        $registration = EventRegistration::register(
            event: $event,
            person: $person,
            now: new \DateTimeImmutable('2026-07-01 10:00:00'),
        );

        $event->getRegistrations()->add($registration);

        self::assertTrue($this->policy->hasAvailableCapacity($event));

        $this->policy->assertHasAvailableCapacity($event);

        self::assertTrue(true);
    }

    public function testLimitedEventWithoutAvailableCapacityIsRejected(): void
    {
        $organization = OrganizationFactory::createOne();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );
        $event->changeCapacity(1);

        $person = PersonFactory::new()->forOrganization($organization)->create();

        $registration = EventRegistration::register(
            event: $event,
            person: $person,
            now: new \DateTimeImmutable('2026-07-01 10:00:00'),
        );

        $event->getRegistrations()->add($registration);

        self::assertFalse($this->policy->hasAvailableCapacity($event));

        $this->expectException(EventCapacityExceededException::class);

        $this->policy->assertHasAvailableCapacity($event);
    }

    public function testWaitlistedRegistrationDoesNotConsumeCapacity(): void
    {
        $organization = OrganizationFactory::createOne();

        $event = Event::create(
            organization: $organization,
            title: 'Cours d’initiation',
            startsAt: new \DateTimeImmutable('2026-07-10 09:00:00'),
            endsAt: new \DateTimeImmutable('2026-07-10 17:00:00'),
        );
        $event->changeCapacity(1);

        $person = PersonFactory::new()->forOrganization($organization)->create();

        $registration = EventRegistration::waitlist(
            event: $event,
            person: $person,
            now: new \DateTimeImmutable('2026-07-01 10:00:00'),
        );

        $event->getRegistrations()->add($registration);

        self::assertTrue($this->policy->hasAvailableCapacity($event));
        self::assertSame(0, $event->getRegisteredCount());
    }
}
